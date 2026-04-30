<?php

declare(strict_types=1);

namespace App\Livewire\Skills;

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Services\SkillEvolutionService;
use App\Services\SkillHintService;
use App\Services\SkillService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SkillCatalog extends Component
{
    // Filter state
    public string $search = '';

    public string $filter = 'all'; // all | owned | available | hints | evolve

    public string $typeFilter = '';

    // Selected character
    public ?int $characterId = null;

    // Evolution modal
    public bool $showEvolutionModal = false;

    public ?int $evolutionSkillId = null;

    // Toast
    public ?string $toastMessage = null;

    public string $toastType = 'success';

    /**
     * Mount the component with optional character pre-selection.
     */
    public function mount(?int $characterId = null): void
    {
        if ($characterId) {
            $this->characterId = $characterId;
        } else {
            // Auto-select first character for authenticated user
            $character = Character::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->first();
            $this->characterId = $character?->id;
        }
    }

    /**
     * Get the active character.
     */
    #[Computed]
    public function character(): ?Character
    {
        if (! $this->characterId) {
            return null;
        }

        return Character::where('id', $this->characterId)
            ->where('user_id', Auth::id())
            ->first();
    }

    /**
     * Get all characters for the selector.
     *
     * @return Collection<int, Character>
     */
    #[Computed]
    public function characters(): Collection
    {
        return Character::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name']);
    }

    /**
     * Get owned skill IDs for the active character.
     *
     * @return array<int>
     */
    #[Computed]
    public function ownedSkillIds(): array
    {
        if (! $this->characterId) {
            return [];
        }

        return SkillAcquisition::where('character_id', $this->characterId)
            ->where('is_active', true)
            ->pluck('skill_id')
            ->toArray();
    }

    /**
     * Get hint counts per skill for the active character.
     *
     * @return array<int, int>
     */
    #[Computed]
    public function hintCounts(): array
    {
        if (! $this->characterId) {
            return [];
        }

        return \App\Models\SkillHint::where('character_id', $this->characterId)
            ->where('is_used', false)
            ->selectRaw('skill_id, COUNT(*) as cnt')
            ->groupBy('skill_id')
            ->pluck('cnt', 'skill_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    /**
     * Get filtered skills.
     *
     * @return Collection<int, Skill>
     */
    #[Computed]
    public function skills(): Collection
    {
        $query = Skill::where('is_active', true)
            ->with(['evolutionTarget', 'evolutionSource']);

        // Search
        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($this->typeFilter !== '') {
            $query->where('skill_type', $this->typeFilter);
        }

        // Tab filter
        $ownedIds = $this->ownedSkillIds;
        $hintCounts = $this->hintCounts;

        switch ($this->filter) {
            case 'owned':
                $query->whereIn('id', $ownedIds);
                break;
            case 'available':
                $query->whereNotIn('id', $ownedIds);
                break;
            case 'hints':
                $query->whereIn('id', array_keys($hintCounts));
                break;
            case 'evolve':
                $query->where('can_evolve', true)->whereIn('id', $ownedIds);
                break;
        }

        return $query->orderByRaw("CASE meta_tier WHEN 'S' THEN 1 WHEN 'A' THEN 2 WHEN 'B' THEN 3 WHEN 'C' THEN 4 ELSE 5 END")
            ->orderBy('base_sp_cost', 'asc')
            ->get();
    }

    /**
     * SP budget data for the active character.
     *
     * @return array{available: int, total_earned: int, used: int}
     */
    #[Computed]
    public function spBudget(): array
    {
        $character = $this->character;
        if (! $character) {
            return ['available' => 0, 'total_earned' => 0, 'used' => 0];
        }

        $available = (int) ($character->available_sp ?? 0);
        $used = (int) SkillAcquisition::where('character_id', $this->characterId)
            ->where('is_active', true)
            ->sum('final_sp_cost');
        $totalEarned = $available + $used;

        return [
            'available' => $available,
            'total_earned' => $totalEarned,
            'used' => $used,
        ];
    }

    /**
     * Count of owned skills.
     */
    #[Computed]
    public function ownedCount(): int
    {
        return \count($this->ownedSkillIds);
    }

    /**
     * Count of skills with hints.
     */
    #[Computed]
    public function hintCount(): int
    {
        return \count($this->hintCounts);
    }

    /**
     * Count of evolvable skills.
     */
    #[Computed]
    public function evolveCount(): int
    {
        $ownedIds = $this->ownedSkillIds;

        return Skill::where('can_evolve', true)
            ->whereIn('id', $ownedIds)
            ->count();
    }

    /**
     * Calculate discounted cost for a skill.
     */
    public function getDiscountedCost(int $skillId, int $baseCost): int
    {
        $hintCount = $this->hintCounts[$skillId] ?? 0;
        if ($hintCount === 0) {
            return $baseCost;
        }

        $discount = match (true) {
            $hintCount >= 5 => 40,
            $hintCount === 4 => 35,
            $hintCount === 3 => 30,
            $hintCount === 2 => 20,
            $hintCount === 1 => 10,
            default => 0,
        };

        return (int) round($baseCost * (1 - $discount / 100));
    }

    /**
     * Open the evolution modal for a skill.
     */
    public function openEvolutionModal(int $skillId): void
    {
        $this->evolutionSkillId = $skillId;
        $this->showEvolutionModal = true;
    }

    /**
     * Close the evolution modal.
     */
    public function closeEvolutionModal(): void
    {
        $this->showEvolutionModal = false;
        $this->evolutionSkillId = null;
    }

    /**
     * Get the skill being evolved (for the modal).
     */
    #[Computed]
    public function evolutionSkill(): ?Skill
    {
        if (! $this->evolutionSkillId) {
            return null;
        }

        return Skill::with(['evolutionTarget', 'evolutionSource'])->find($this->evolutionSkillId);
    }

    /**
     * Confirm skill evolution.
     */
    public function confirmEvolution(SkillEvolutionService $evolutionService): void
    {
        $character = $this->character;
        $skill = $this->evolutionSkill;

        if (! $character || ! $skill) {
            $this->showToast('Character or skill not found.', 'error');

            return;
        }

        $result = $evolutionService->evolveSkill($character, $skill);

        if ($result['success']) {
            $this->showToast("✨ {$skill->name} evolved successfully!", 'success');
            $this->closeEvolutionModal();
            unset($this->ownedSkillIds, $this->skills, $this->evolveCount);
        } else {
            $this->showToast($result['message'] ?? 'Evolution failed.', 'error');
        }
    }

    /**
     * Purchase a skill for the active character.
     */
    public function purchaseSkill(int $skillId, SkillService $skillService, SkillHintService $hintService): void
    {
        $character = $this->character;
        if (! $character) {
            $this->showToast('No character selected.', 'error');

            return;
        }

        $skill = Skill::find($skillId);
        if (! $skill) {
            $this->showToast('Skill not found.', 'error');

            return;
        }

        $hintCount = $this->hintCounts[$skillId] ?? 0;
        $finalCost = $skillService->calculateFinalCost($skill->base_sp_cost, $hintCount);

        if (($character->available_sp ?? 0) < $finalCost) {
            $this->showToast("Not enough SP. Need {$finalCost} SP.", 'error');

            return;
        }

        // Create acquisition record
        SkillAcquisition::create([
            'character_id' => $character->id,
            'skill_id' => $skillId,
            'career_id' => $character->currentCareer?->id,
            'turn_acquired' => $character->current_turn ?? 1,
            'career_phase' => $character->career_stage ?? 'junior',
            'acquisition_method' => 'purchase',
            'base_sp_cost' => $skill->base_sp_cost,
            'hints_used' => $hintCount,
            'final_sp_cost' => $finalCost,
            'sp_saved' => $skill->base_sp_cost - $finalCost,
            'is_active' => true,
        ]);

        // Deduct SP
        $character->decrement('available_sp', $finalCost);

        // Mark hints as used
        if ($hintCount > 0) {
            $hintService->markHintsAsUsed($character, $skill);
        }

        $this->showToast("✅ {$skill->name} purchased for {$finalCost} SP!", 'success');
        unset($this->ownedSkillIds, $this->skills, $this->spBudget, $this->ownedCount, $this->hintCounts);
    }

    /**
     * Show a toast notification.
     */
    protected function showToast(string $message, string $type = 'success'): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
        $this->dispatch('toast-shown');
    }

    /**
     * Dismiss the toast.
     */
    public function dismissToast(): void
    {
        $this->toastMessage = null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.skills.skill-catalog');
    }
}
