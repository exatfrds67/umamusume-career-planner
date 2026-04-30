<?php

declare(strict_types=1);

namespace App\Livewire\Characters;

use App\Models\Character;
use App\Models\GameCharacter;
use App\Models\SupportCardDefinition;
use App\Services\CharacterGameDataResolver;
use App\Services\CharacterManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CharacterWizard extends Component
{
    public int $step = 1;

    public string $search = '';

    public string $rarityFilter = 'all';

    public string $selectedScenario = 'ura_finale';

    public ?int $selectedTraineeId = null;

    public ?int $selectedFatherId = null;

    public ?int $selectedMotherId = null;

    public int $activeSlot = -1;

    /**
     * @var array<int, int|null>
     */
    public array $supportDeck = [null, null, null, null, null, null];

    public function nextStep(): void
    {
        if ($this->canAdvance() && $this->step < 4) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function canAdvance(): bool
    {
        return match ($this->step) {
            1 => $this->selectedTraineeId !== null,
            2 => $this->selectedFatherId !== null && $this->selectedMotherId !== null,
            3 => count(array_filter($this->supportDeck)) > 0,
            default => false,
        };
    }

    public function selectTrainee(int $traineeId): void
    {
        $this->selectedTraineeId = $traineeId;
    }

    public function selectScenario(string $scenario): void
    {
        $this->selectedScenario = $scenario;
    }

    public function selectFather(int $characterId): void
    {
        $this->selectedFatherId = $characterId;
    }

    public function selectMother(int $characterId): void
    {
        $this->selectedMotherId = $characterId;
    }

    public function setActiveSlot(int $slot): void
    {
        $this->activeSlot = ($this->activeSlot === $slot) ? -1 : $slot;
    }

    public function assignSupport(int $cardId): void
    {
        if ($this->activeSlot < 0 || ! array_key_exists($this->activeSlot, $this->supportDeck)) {
            return;
        }

        $this->supportDeck[$this->activeSlot] = $cardId;
        $this->activeSlot = -1;
    }

    public function clearSupport(int $slot): void
    {
        if (! array_key_exists($slot, $this->supportDeck)) {
            return;
        }

        $this->supportDeck[$slot] = null;

        if ($this->activeSlot === $slot) {
            $this->activeSlot = -1;
        }
    }

    /**
     * Create the character and redirect to the detail view.
     */
    public function createCharacter(CharacterManagementService $service): void
    {
        if (! $this->selectedTraineeId) {
            $this->addError('creation', 'Please select a trainee before creating.');

            return;
        }

        $trainee = GameCharacter::find($this->selectedTraineeId);
        $name = $trainee?->name_en ?? 'New Character';

        $character = $service->create([
            'trainee_id' => $this->selectedTraineeId,
            'name' => $name,
            'scenario_type' => $this->selectedScenario,
            'stats' => ['speed' => 0, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 0],
        ]);

        Cache::forget('character_index_v2_'.Auth::id().'_'.md5(''));

        $this->redirect(route('characters.show', $character), navigate: true);
    }

    /**
     * @return array<string, int>
     */
    public function getInheritancePreviewProperty(): array
    {
        return ['speed' => 0, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 0];
    }

    public function render(): View
    {
        $trainees = Cache::remember('wizard_trainees_with_images_v2', 3600, function (): \Illuminate\Support\Collection {
            $resolver = new CharacterGameDataResolver;

            return GameCharacter::query()
                ->select(['id', 'name_en', 'name_jp', 'image_path', 'primary_distance', 'preferred_style'])
                ->orderBy('name_en')
                ->get()
                ->map(function (GameCharacter $trainee) use ($resolver): GameCharacter {
                    $trainee->avatar_url = $resolver->resolveAvatarUrlForGameCharacter($trainee);

                    return $trainee;
                });
        });

        $parents = Cache::remember('wizard_parents_'.Auth::id(), 3600, function (): \Illuminate\Database\Eloquent\Collection {
            return Character::query()
                ->select(['id', 'name', 'avatar_url', 'current_stats', 'scenario_type'])
                ->where(function ($query): void {
                    $query->where('user_id', Auth::id())
                        ->orWhere('is_seeded', true);
                })
                ->orderByDesc('updated_at')
                ->limit(12)
                ->get();
        });

        $supportCards = Cache::remember('wizard_support_cards', 86400, function (): \Illuminate\Database\Eloquent\Collection {
            return SupportCardDefinition::query()
                ->select(['id', 'name', 'rarity', 'card_type'])
                ->orderBy('rarity')
                ->orderBy('name')
                ->limit(24)
                ->get();
        });

        $selectedFather = $this->selectedFatherId
            ? $parents->firstWhere('id', $this->selectedFatherId)
            : null;

        $selectedMother = $this->selectedMotherId
            ? $parents->firstWhere('id', $this->selectedMotherId)
            : null;

        $filteredTrainees = $this->search !== ''
            ? $trainees->filter(fn (GameCharacter $t) => str_contains(
                mb_strtolower($t->name_en ?? ''),
                mb_strtolower($this->search)
            ))
            : $trainees;

        return view('livewire.characters.wizard', [
            'trainees' => $filteredTrainees,
            'parents' => $parents,
            'supportCards' => $supportCards,
            'selectedFather' => $selectedFather,
            'selectedMother' => $selectedMother,
            'canAdvance' => $this->canAdvance(),
        ]);
    }
}
