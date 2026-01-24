<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\CharacterStateService;
use App\Services\TrainingPredictionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function __construct(
        protected TrainingPredictionService $trainingService,
        protected CharacterStateService $stateService
    ) {}

    /**
     * Show the training selection screen.
     */
    public function index(Character $character): View
    {
        if ($character->user_id !== Auth::id()) {
            abort(403);
        }

        // Prepare data for the view
        $trainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit'];
        $trainingData = [];

        foreach ($trainingTypes as $type) {
            $gains = $this->trainingService->calculateGain($character, $type);
            $failureRate = $this->trainingService->calculateFailureRate($character, $type);

            $trainingData[$type] = [
                'gains' => $gains['stats'],
                'energy_cost' => $gains['energy'],
                'failure_rate' => $failureRate,
            ];
        }

        return view('training.index', compact('character', 'trainingData'));
    }

    /**
     * Execute a training action.
     */
    public function store(Request $request, Character $character): RedirectResponse
    {
        if ($character->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'training_type' => 'required|in:speed,stamina,power,guts,wit',
        ]);

        $trainingType = $request->input('training_type');
        $type = is_string($trainingType) ? $trainingType : 'speed';

        // Execute training
        $result = $this->trainingService->executeTraining($character, $type);

        // Advance turn (Training consumes 1 turn)
        $turnResult = $this->stateService->progressTurn($character);

        // Build message
        if ($result['success']) {
            $msg = 'Training Successful! ';
            /** @var array<string, int|float> $gains */
            $gains = is_array($result['gains']) ? $result['gains'] : [];
            foreach ($gains as $stat => $gain) {
                if (is_string($stat) && (is_int($gain) || is_float($gain))) {
                    $msg .= ucfirst($stat)."+{$gain} ";
                }
            }
        } else {
            $msg = 'Training Failed... Mood worsened and energy dropped.';
        }

        return redirect()->route('training.index', $character)->with($result['success'] ? 'success' : 'error', $msg);
    }
}
