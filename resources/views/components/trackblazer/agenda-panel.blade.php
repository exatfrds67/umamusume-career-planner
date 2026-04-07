<?php

use Livewire\Volt\Component;
use App\Models\Career;
use App\Services\AgendaService;

new class extends Component
{
    public Career $career;
    
    public int $reservedCoins = 0;
    public int $reservedHammers = 0;
    public ?string $targetTsDistance = null;

    public function mount(Career $career)
    {
        $this->career = $career;
        $svc = app(AgendaService::class);
        $reservation = $svc->getReservations($career);
        if ($reservation) {
            $this->reservedCoins = $reservation->reserved_coins;
            $this->reservedHammers = $reservation->reserved_hammers;
            $this->targetTsDistance = $reservation->target_ts_distance;
        }
    }

    public function saveReservation()
    {
        $svc = app(AgendaService::class);
        $svc->reserveItems(
            $this->career,
            $this->reservedCoins,
            $this->reservedHammers,
            $this->targetTsDistance
        );
        $this->dispatch('agenda-reserved');
    }
};
?>

<div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Trackblazer Agenda Planning</h3>
    
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reserved Coins</label>
            <input type="number" wire:model="reservedCoins" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reserved Hammers</label>
            <input type="number" wire:model="reservedHammers" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Target TS Distance</label>
            <select wire:model="targetTsDistance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">-- None --</option>
                <option value="sprint">Sprint</option>
                <option value="mile">Mile</option>
                <option value="medium">Medium</option>
                <option value="long">Long</option>
                <option value="dirt">Dirt</option>
            </select>
        </div>

        <button wire:click="saveReservation" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
            Save Agenda Reservation
        </button>
    </div>
    
    <div wire:loading wire:target="saveReservation" class="mt-2 text-sm text-gray-500">
        Saving...
    </div>
</div>
