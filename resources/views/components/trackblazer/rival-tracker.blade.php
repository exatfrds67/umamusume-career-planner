<?php

use Livewire\Volt\Component;
use App\Models\Career;
use App\Services\TrackblazerService;
use App\Enums\TsDistance;
use App\Enums\RivalRaceOutcome;

new class extends Component
{
    public Career $career;
    
    public int $raceId = 1;
    public string $distance = 'sprint';
    public string $rivalUma = '';
    public string $outcome = 'won';
    public string $skillHintStr = '';

    public array $logs = [];

    public function mount(Career $career)
    {
        $this->career = $career;
        $this->loadLogs();
    }

    public function loadLogs()
    {
        $svc = app(TrackblazerService::class);
        $this->logs = $svc->getRivalLogs($this->career)->toArray();
    }

    public function logRival()
    {
        $this->validate([
            'raceId' => 'required|numeric',
            'distance' => 'required|string',
            'rivalUma' => 'required|string',
            'outcome' => 'required|string',
        ]);

        $hints = array_filter(array_map('trim', explode(',', $this->skillHintStr)));

        $svc = app(TrackblazerService::class);
        $svc->logRivalEncounter(
            $this->career,
            (int) $this->raceId,
            TsDistance::from($this->distance),
            $this->rivalUma,
            RivalRaceOutcome::from($this->outcome),
            $hints
        );

        $this->reset(['rivalUma', 'skillHintStr']);
        $this->loadLogs();
    }
};
?>

<div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Log Rival Encounter</h3>
    
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm">Race ID / Turn</label>
            <input type="number" wire:model="raceId" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm">Distance</label>
            <select wire:model="distance" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="sprint">Sprint</option>
                <option value="mile">Mile</option>
                <option value="medium">Medium</option>
                <option value="long">Long</option>
                <option value="dirt">Dirt</option>
            </select>
        </div>
        <div>
            <label class="block text-sm">Rival Uma Name</label>
            <input type="text" wire:model="rivalUma" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm">Outcome</label>
            <select wire:model="outcome" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="won">Won</option>
                <option value="lost">Lost</option>
                <option value="did_not_race">Did Not Race</option>
            </select>
        </div>
        <div class="col-span-2">
            <label class="block text-sm">Skill Hints (comma separated)</label>
            <input type="text" wire:model="skillHintStr" class="mt-1 block w-full rounded-md border-gray-300">
        </div>
    </div>
    
    <button wire:click="logRival" class="mb-4 inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
        Log Encounter
    </button>

    <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300 border-b pb-2 mb-2">Recent Logs</h4>
    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
        @forelse($logs as $log)
            <li class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                Turn {{ $log['race_id'] }} - {{ ucfirst($log['distance']) }}: vs {{ $log['rival_uma'] }} ({{ ucfirst($log['outcome']) }})
                @if(count($log['skill_hints'] ?? []))
                    <br><span class="text-xs text-indigo-500">Hints: {{ implode(', ', $log['skill_hints']) }}</span>
                @endif
            </li>
        @empty
            <li>No rival encounters logged yet.</li>
        @endforelse
    </ul>
</div>
