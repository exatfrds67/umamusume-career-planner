<div class="min-h-screen" style="background: #F9F5FF">
    {{-- Page Header --}}
    <div class="px-6 py-6 md:px-8 md:py-8" style="background: #FFFFFF; border-bottom: 1px solid #EDE9FE">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black" style="color: #1E1033">Race Strategy</h1>
                    @if ($character)
                        <p class="text-sm font-semibold mt-1" style="color: #7C6FAB">
                            {{ $character->name }} · Turn {{ $character->current_turn }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Content Container --}}
    <div class="max-w-7xl mx-auto px-6 py-8 md:px-8">
        {{-- Tab Bar --}}
        <div class="flex gap-2 mb-6" style="background: #EDE9FE; border-radius: 12px; padding: 4px; width: fit-content">
            <button wire:click="switchTab('upcoming')" class="px-6 py-2 font-semibold rounded-lg transition-all"
                style="
                    {{ $activeTab === 'upcoming' ? 'background: #FFFFFF; color: #7C3AED; box-shadow: 0 1px 6px rgba(124,58,237,0.15)' : 'background: transparent; color: #7C6FAB' }};
                    border-radius: 9px;
                    font-size: 14px;
                    font-weight: 700;
                ">
                Upcoming
            </button>
            <button wire:click="switchTab('history')" class="px-6 py-2 font-semibold rounded-lg transition-all"
                style="
                    {{ $activeTab === 'history' ? 'background: #FFFFFF; color: #7C3AED; box-shadow: 0 1px 6px rgba(124,58,237,0.15)' : 'background: transparent; color: #7C6FAB' }};
                    border-radius: 9px;
                    font-size: 14px;
                    font-weight: 700;
                ">
                History
            </button>
        </div>

        {{-- Tab Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-[340px,1fr] gap-6">
            {{-- Left Panel: Race List --}}
            <div class="space-y-3" style="max-height: 600px; overflow-y: auto">
                @if ($activeTab === 'upcoming' && count($upcomingRaces) > 0)
                    @foreach ($upcomingRaces as $race)
                        <button wire:click="selectRace({{ $race['id'] }})"
                            class="w-full text-left p-4 rounded-lg border-2 transition-all"
                            style="
                                {{ $selectedRaceId === $race['id']
                                    ? 'border-color: #7C3AED; background: linear-gradient(135deg,#F5F3FF,#EDE9FE)'
                                    : 'border-color: #EDE9FE; background: #FFFFFF' }};
                            ">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-sm" style="color: #1E1033">{{ $race['name'] }}</h3>
                                <span class="text-xs px-2 py-1 rounded font-bold text-white"
                                    style="
                                        {{ match ($race['grade']) {
                                            'G1' => 'background: linear-gradient(135deg,#F59E0B,#F97316)',
                                            'G2' => 'background: #EDE9FE; color: #7C3AED',
                                            default => 'background: #F3F4F6; color: #6B7280',
                                        } }}
                                    ">
                                    {{ $race['grade'] }}
                                </span>
                            </div>

                            <p class="text-xs mb-2" style="color: #7C6FAB">
                                {{ $race['distance'] }} · {{ $race['surface'] }} · Turn {{ $race['turn'] }}
                                @if ($race['required'] ?? false)
                                    <span class="ml-2 text-xs font-bold" style="color: #EF4444">Required</span>
                                @endif
                            </p>

                            {{-- Readiness Bar --}}
                            <div class="space-y-1">
                                <div class="w-full bg-gray-200 rounded-full h-1.5" style="background: #EDE9FE">
                                    @php
                                        $readiness = $race['readiness'] ?? 50;
                                        $readinessColor =
                                            $readiness >= 80 ? '#10B981' : ($readiness >= 60 ? '#F59E0B' : '#EF4444');
                                    @endphp
                                    <div class="h-full rounded-full transition-all"
                                        style="width: {{ min($readiness, 100) }}%; background: {{ $readinessColor }}">
                                    </div>
                                </div>
                                <p class="text-xs font-semibold" style="color: {{ $readinessColor }}">
                                    {{ $readiness }}%
                                    {{ match (true) {
                                        $readiness >= 80 => 'Excellent',
                                        $readiness >= 70 => 'Good',
                                        $readiness >= 55 => 'Fair',
                                        default => 'Poor',
                                    } }}
                                </p>
                            </div>
                        </button>
                    @endforeach
                @elseif ($activeTab === 'history' && count($historyRaces) > 0)
                    @foreach ($historyRaces as $race)
                        @php
                            $placement = $race->finish_position ?? 0;
                            $placementStr = $placement > 0 ? (string) $placement : '—';
                            $placementBg = match ($placement) {
                                1 => 'linear-gradient(135deg,#F59E0B,#F97316)',
                                2 => 'linear-gradient(135deg,#C4B5FD,#7C3AED)',
                                3 => '#D1D5DB',
                                default => '#EDE9FE',
                            };
                            $placementTextColor = $placement <= 3 ? '#FFFFFF' : '#7C6FAB';
                            $suffix = match ($placement) {
                                1 => 'st',
                                2 => 'nd',
                                3 => 'rd',
                                default => 'th',
                            };
                        @endphp
                        <div class="p-4 rounded-lg"
                            style="background: {{ $placement === 1 ? '#FFFBEB' : '#F9F5FF' }}; border: 1px solid {{ $placement === 1 ? '#FCD34D' : '#EDE9FE' }}; border-radius: 12px; margin-bottom: 8px">
                            <div class="flex items-start gap-3">
                                <div class="w-11 h-11 rounded-xl font-bold flex items-center justify-center text-sm flex-shrink-0"
                                    style="background: {{ $placementBg }}; color: {{ $placementTextColor }}; border-radius: 12px">
                                    {{ $placementStr }}<sup class="text-xs">{{ $suffix }}</sup>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-sm" style="color: #1E1033">
                                        {{ $race->race_name ?? 'Unknown Race' }}</h3>
                                    <p class="text-xs mt-1" style="color: #7C6FAB">
                                        @if ($race->race_grade)
                                            <span class="font-bold">{{ $race->race_grade }}</span> ·
                                        @endif
                                        Turn {{ $race->turn_number ?? '—' }}
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    @if ($race->fans_gained)
                                        <p class="text-xs font-semibold" style="color: #E879A0">
                                            👥 +{{ number_format($race->fans_gained) }}
                                        </p>
                                    @endif
                                    @if ($race->sp_reward)
                                        <p class="text-xs font-semibold mt-1" style="color: #F59E0B">
                                            ✨ +{{ $race->sp_reward }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12" style="color: #7C6FAB">
                        <p class="text-sm">
                            @if ($activeTab === 'upcoming')
                                No upcoming races available
                            @else
                                No race history recorded
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            {{-- Right Panel: Race Detail --}}
            @if ($selectedRace)
                <div class="p-6 rounded-2xl"
                    style="background: #FFFFFF; border: 1px solid #EDE9FE; box-shadow: 0 2px 12px rgba(124,58,237,0.07)">
                    {{-- Race Header --}}
                    <div class="mb-6 pb-6" style="border-bottom: 1px solid #EDE9FE">
                        <div class="flex justify-between items-start mb-2">
                            <h2 class="text-xl font-black" style="color: #1E1033">{{ $selectedRace['name'] ?? 'Race' }}
                            </h2>
                            <span class="text-xs px-3 py-1.5 rounded font-bold text-white"
                                style="
                                    {{ match ($selectedRace['grade'] ?? 'G3') {
                                        'G1' => 'background: linear-gradient(135deg,#F59E0B,#F97316)',
                                        'G2' => 'background: #EDE9FE; color: #7C3AED',
                                        default => 'background: #F3F4F6; color: #6B7280',
                                    } }}
                                ">
                                {{ $selectedRace['grade'] ?? 'G3' }}
                            </span>
                        </div>
                        <p class="text-sm" style="color: #7C6FAB">
                            {{ $selectedRace['distance'] ?? '2000m' }} · {{ $selectedRace['surface'] ?? 'Turf' }} ·
                            Turn {{ $selectedRace['turn'] ?? 'N/A' }}
                        </p>
                    </div>

                    {{-- Readiness Section --}}
                    <div class="mb-6 pb-6" style="border-bottom: 1px solid #EDE9FE">
                        <p class="text-xs font-bold mb-3"
                            style="color: #7C6FAB; letter-spacing: 1.5px; text-transform: uppercase">Readiness</p>
                        @php
                            $readiness = $selectedRace['readiness'] ?? 50;
                            $readinessColor = $readiness >= 80 ? '#10B981' : ($readiness >= 60 ? '#F59E0B' : '#EF4444');
                            $readinessLabel = match (true) {
                                $readiness >= 80 => 'Excellent',
                                $readiness >= 70 => 'Good',
                                $readiness >= 55 => 'Fair',
                                default => 'Poor',
                            };
                        @endphp
                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-3xl font-black"
                                style="color: {{ $readinessColor }}">{{ $readiness }}%</span>
                            <span class="text-sm font-bold"
                                style="color: {{ $readinessColor }}">{{ $readinessLabel }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2" style="background: #EDE9FE; height: 6px">
                            <div class="h-full rounded-full"
                                style="width: {{ min($readiness, 100) }}%; background: {{ $readinessColor }}"></div>
                        </div>
                    </div>

                    {{-- Stat Requirements --}}
                    <div class="mb-6 pb-6" style="border-bottom: 1px solid #EDE9FE">
                        <p class="text-xs font-bold mb-3"
                            style="color: #7C6FAB; letter-spacing: 1.5px; text-transform: uppercase">Stat Requirements
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            @php
                                $stats = [
                                    [
                                        'name' => 'Speed',
                                        'value' => $selectedRace['requirements']['speed'] ?? 700,
                                        'color' => '#E879A0',
                                        'icon' => '⚡',
                                    ],
                                    [
                                        'name' => 'Stamina',
                                        'value' => $selectedRace['requirements']['stamina'] ?? 700,
                                        'color' => '#10B981',
                                        'icon' => '🌿',
                                    ],
                                    [
                                        'name' => 'Power',
                                        'value' => $selectedRace['requirements']['power'] ?? 700,
                                        'color' => '#F59E0B',
                                        'icon' => '🔥',
                                    ],
                                    [
                                        'name' => 'Guts',
                                        'value' => $selectedRace['requirements']['guts'] ?? 700,
                                        'color' => '#EF4444',
                                        'icon' => '❤️',
                                    ],
                                    [
                                        'name' => 'Wit',
                                        'value' => $selectedRace['requirements']['wit'] ?? 700,
                                        'color' => '#3B82F6',
                                        'icon' => '💙',
                                    ],
                                ];
                            @endphp
                            @foreach ($stats as $stat)
                                <div class="p-3 rounded-lg" style="background: #F9F5FF; border: 1px solid #EDE9FE">
                                    <p class="text-xs mb-1" style="color: #7C6FAB">{{ $stat['name'] }}</p>
                                    <p class="text-lg font-bold" style="color: {{ $stat['color'] }}">
                                        {{ $stat['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Recommended Style --}}
                    <div class="mb-6 pb-6 p-4 rounded-lg"
                        style="background: linear-gradient(135deg,#F5F3FF,#EDE9FE); border: 1px solid #C4B5FD">
                        <p class="text-xs font-bold mb-2" style="color: #7C3AED; letter-spacing: 1px">🤖 Recommended
                            Style</p>
                        <p class="text-base font-bold" style="color: #1E1033">
                            {{ $selectedRace['recommendedStyle'] ?? 'Front Runner 逃げ' }}</p>
                    </div>

                    {{-- Performance Forecast --}}
                    <div class="mb-6">
                        <p class="text-xs font-bold mb-3"
                            style="color: #7C6FAB; letter-spacing: 1.5px; text-transform: uppercase">Performance
                            Forecast</p>
                        <div class="space-y-2">
                            @php
                                $placementProbs = [
                                    ['place' => '1st', 'prob' => 45, 'color' => '#F59E0B'],
                                    ['place' => '2nd', 'prob' => 30, 'color' => '#C4B5FD'],
                                    ['place' => '3rd', 'prob' => 15, 'color' => '#D1D5DB'],
                                ];
                            @endphp
                            @foreach ($placementProbs as $item)
                                <div class="flex items-center gap-3">
                                    <p class="text-xs font-bold w-12" style="color: #7C6FAB">{{ $item['place'] }}</p>
                                    <div class="flex-1 bg-gray-200 rounded-full h-2"
                                        style="background: #EDE9FE; height: 6px">
                                        <div class="h-full rounded-full"
                                            style="width: {{ $item['prob'] }}%; background: {{ $item['color'] }}">
                                        </div>
                                    </div>
                                    <p class="text-xs font-bold w-8 text-right" style="color: {{ $item['color'] }}">
                                        {{ $item['prob'] }}%</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3 pt-4" style="border-top: 1px solid #EDE9FE">
                        <button class="flex-1 px-4 py-3 rounded-lg font-bold text-white transition-all"
                            style="background: linear-gradient(135deg,#E879A0,#7C3AED); box-shadow: 0 4px 12px rgba(232,121,160,.35)"
                            @click="$dispatch('open-race-modal', { raceId: {{ $selectedRaceId }} })">
                            Enter Race
                        </button>
                        <button class="flex-1 px-4 py-3 rounded-lg font-bold transition-all"
                            style="background: #EDE9FE; color: #7C3AED">
                            Run Simulation
                        </button>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center p-12 rounded-2xl"
                    style="background: #F9F5FF; border: 2px dashed #C4B5FD">
                    <p class="text-center" style="color: #7C6FAB">
                        @if ($activeTab === 'upcoming')
                            Select a race to view details
                        @else
                            Select a race from history
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
