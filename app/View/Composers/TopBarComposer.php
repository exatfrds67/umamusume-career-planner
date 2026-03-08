<?php

namespace App\View\Composers;

use App\Models\Character;
use App\Models\CriticalAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class TopBarComposer
{
    /**
     * Bind shared top bar data for the layout.
     *
     * Uses View::share so that data is available to all views in the request.
     * Controller-specific $topStatus will override the shared default since
     * view-specific data takes precedence over View::share data.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user) {
            ViewFacade::share([
                'activeCharacters' => collect(),
                'currentCharacter' => null,
                'criticalAlertCount' => 0,
            ]);

            return;
        }

        // Get user's active characters for the Run Selector
        $activeCharacters = Character::where('user_id', '=', $user->id, 'and')
            ->whereIn('status', ['active', 'completed'], 'and', false)
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get(['id', 'name', 'scenario_type', 'current_turn', 'status', 'career_stage', 'energy_level', 'mood_status', 'available_sp', 'is_pinned']);

        // Determine current character from session or first active
        $currentCharacterId = session('current_character_id');
        $currentCharacter = null;

        if ($currentCharacterId) {
            $currentCharacter = $activeCharacters->firstWhere('id', $currentCharacterId);
        }

        if (! $currentCharacter) {
            $currentCharacter = $activeCharacters->firstWhere('status', 'active') ?? $activeCharacters->first();
        }

        /** @var Character|null $currentCharacter */

        // Provide default topStatus from the current character.
        // Controllers that pass their own $topStatus will override this.
        if ($currentCharacter instanceof Character) {
            ViewFacade::share('topStatus', [
                'currentTurn' => $currentCharacter->current_turn,
                'maxTurns' => $currentCharacter->getMaxTurns(),
                'spAvailable' => $currentCharacter->available_sp ?? 0,
                'storageMode' => 'account',
                'energy' => $currentCharacter->energy_level,
                'mood' => $currentCharacter->mood_status,
                'careerStage' => $currentCharacter->career_stage,
            ]);
        }

        // Count active critical alerts for the badge
        $criticalAlertCount = 0;
        if ($currentCharacter instanceof Character) {
            $criticalAlertCount = CriticalAlert::whereHas('career', function ($query) use ($currentCharacter) {
                $query->where('character_id', $currentCharacter->id);
            })->active()->count();
        }

        ViewFacade::share([
            'activeCharacters' => $activeCharacters,
            'currentCharacter' => $currentCharacter,
            'criticalAlertCount' => $criticalAlertCount,
        ]);
    }
}
