<?php

namespace App\Livewire\Analytics;

use App\Services\Analytics\CareerComparisonAnalyticsService;
use App\Services\Analytics\PatternRecognitionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PatternDashboard extends Component
{
    /** @var array<int, int> */
    public array $selectedCareerIds = [];

    /** @var array<string, mixed> */
    public array $clusterResults = [];

    /** @var array<string, mixed> */
    public array $associationRules = [];

    /** @var array<string, mixed> */
    public array $comparisonSummary = [];

    /** @var array<string, mixed> */
    public array $divergenceData = [];

    /** @var array<string, mixed> */
    public array $progressionData = [];

    /** @var array<string, mixed> */
    public array $parallelCoordinatesData = [];

    public int $clusterCount = 3;

    public bool $isAnalyzing = false;

    public string $activeTab = 'patterns';

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'clusterCount' => ['required', 'integer', 'min:2', 'max:10'],
            'selectedCareerIds' => ['required', 'array', 'min:2'],
            'selectedCareerIds.*' => ['integer', 'exists:ucp_careers,id'],
        ];
    }

    public function mount(): void
    {
        $this->loadDefaultCareers();
    }

    public function loadDefaultCareers(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->selectedCareerIds = $user->characters()
            ->with(['careers' => fn ($q) => $q->where('status', 'completed')->latest()->limit(10)])
            ->get()
            ->pluck('careers')
            ->flatten()
            ->pluck('id')
            ->toArray();
    }

    public function analyzePatterns(): void
    {
        if (count($this->selectedCareerIds) < 2) {
            $this->addError('selectedCareerIds', 'At least 2 careers are required for analysis.');

            return;
        }

        $this->isAnalyzing = true;

        $patternService = app(PatternRecognitionService::class);
        $comparisonService = app(CareerComparisonAnalyticsService::class);

        $careers = \App\Models\Career::query()
            ->whereIn('id', $this->selectedCareerIds)
            ->get();

        $this->clusterResults = $patternService->clusterCareers($careers, $this->clusterCount);
        $this->associationRules = $patternService->mineAssociationRules($careers);

        $this->comparisonSummary = $comparisonService->calculateComparisonSummary($this->selectedCareerIds);
        $this->divergenceData = $comparisonService->identifyDivergencePoints($this->selectedCareerIds);
        $this->progressionData = $comparisonService->generateProgressionChartData($this->selectedCareerIds);
        $this->parallelCoordinatesData = $comparisonService->generateParallelCoordinatesData($this->selectedCareerIds);

        $this->isAnalyzing = false;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render(): \Illuminate\View\View
    {
        $availableCareers = [];
        $user = Auth::user();

        if ($user) {
            $availableCareers = \App\Models\Career::query()
                ->whereHas('character', fn ($q) => $q->where('user_id', $user->id))
                ->where('status', 'completed')
                ->with('character:id,name')
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn ($career) => [
                    'id' => $career->id,
                    'name' => $career->career_name ?? "Career #{$career->id}",
                    'character' => $career->character?->name ?? 'Unknown',
                    'scenario' => $career->scenario_type ?? 'Unknown',
                ])
                ->toArray();
        }

        return view('livewire.analytics.pattern-dashboard', [
            'availableCareers' => $availableCareers,
        ]);
    }
}
