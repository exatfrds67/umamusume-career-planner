<div>
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center"
            style="background: rgba(15,10,40,0.8); backdrop-filter: blur(8px)" wire:click.self="closeModal">
            <div class="bg-white rounded-3xl max-w-md w-full max-h-90vh overflow-y-auto flex flex-col"
                style="box-shadow: 0 20px 60px rgba(124,58,237,0.3)">
                {{-- Modal Header --}}
                <div class="px-7 py-6"
                    style="background: {{ $step === 'result' && $placement <= 3 ? 'linear-gradient(135deg,#064E3B,#065F46)' : ($step === 'result' ? 'linear-gradient(135deg,#7F1D1D,#991B1B)' : 'linear-gradient(135deg,#1E1033,#3B1F6E)') }}">
                    <div class="text-white">
                        @if ($step === 'enter')
                            <p class="text-xs font-semibold" style="color: rgba(255,255,255,0.5); letter-spacing: 1px">🏁
                                ENTERING RACE</p>
                            <h2 class="text-xl font-black mt-2">{{ $race?->name_en ?? 'Race' }}</h2>
                            <p class="text-xs mt-1" style="color: rgba(255,255,255,0.5)">
                                {{ $race?->grade ?? 'G3' }} ·
                                {{ $race?->distance_meters ? number_format($race->distance_meters) . 'm' : '—' }} · Turn
                                {{ $race?->year_in_scenario ?? '—' }}
                            </p>
                        @else
                            <p class="text-xs font-semibold" style="color: rgba(255,255,255,0.5); letter-spacing: 1px">
                                @if ($placement === 1)
                                    🏆 1ST PLACE
                                @elseif ($placement <= 3)
                                    🎖️ TOP 3 FINISH
                                @else
                                    😔 RACE COMPLETE
                                @endif
                            </p>
                            <h2 class="text-2xl font-black mt-2">
                                @if ($placement === 1)
                                    Victory!
                                @elseif ($placement <= 3)
                                    Podium Finish
                                @else
                                    Not Placed
                                @endif
                            </h2>
                            <p class="text-xs mt-1" style="color: rgba(255,255,255,0.5)">{{ $race?->name_en ?? '' }}</p>
                        @endif
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="flex-1 px-7 py-6 overflow-y-auto">
                    @if ($step === 'enter')
                        {{-- Step 1: Placement Selection --}}
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-bold mb-4" style="color: #1E1033">Record Your Placement</p>
                                <div class="grid grid-cols-2 gap-2">
                                    @for ($i = 1; $i <= 8; $i++)
                                        <button wire:click="selectPlacement({{ $i }})"
                                            class="py-3 px-4 rounded-lg border-2 font-bold transition-all"
                                            style="
                                            {{ $placement === $i
                                                ? 'border-color: #E879A0; background: linear-gradient(135deg,#FDF2F8,#F5F3FF); color: #E879A0'
                                                : 'border-color: #EDE9FE; background: #F9F5FF; color: #7C6FAB' }};
                                        ">
                                            {{ $i }}<sup
                                                class="text-xs">{{ match ($i) {1 => 'st',2 => 'nd',3 => 'rd',default => 'th'} }}</sup>
                                        </button>
                                    @endfor
                                </div>
                            </div>

                            {{-- Reward Preview --}}
                            @if ($rewardPreview)
                                <div class="mt-6 p-3 rounded-lg" style="background: #F9F5FF; border: 1px solid #EDE9FE">
                                    <p class="text-xs font-bold mb-3 uppercase"
                                        style="color: #7C6FAB; letter-spacing: 1px">Reward Preview</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="text-center p-2 rounded-lg bg-white"
                                            style="border: 1px solid #EDE9FE">
                                            <p class="text-xs" style="color: #7C6FAB">Fans</p>
                                            <p class="text-lg font-bold mt-1" style="color: #E879A0">
                                                {{ number_format($rewardPreview['fans']) }}</p>
                                        </div>
                                        <div class="text-center p-2 rounded-lg bg-white"
                                            style="border: 1px solid #EDE9FE">
                                            <p class="text-xs" style="color: #7C6FAB">SP</p>
                                            <p class="text-lg font-bold mt-1" style="color: #F59E0B">
                                                {{ $rewardPreview['sp'] }}</p>
                                        </div>
                                        <div class="text-center p-2 rounded-lg bg-white"
                                            style="border: 1px solid #EDE9FE">
                                            <p class="text-xs" style="color: #7C6FAB">Stat Bonus</p>
                                            <p class="text-lg font-bold mt-1" style="color: #10B981">
                                                +{{ $rewardPreview['statBonus'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Step 2: Result Screen --}}
                        @if ($rewardPreview)
                            <div class="space-y-4">
                                <div class="text-center py-4">
                                    @if ($placement === 1)
                                        <p class="text-4xl mb-2">🏆</p>
                                    @elseif ($placement <= 3)
                                        <p class="text-4xl mb-2">🎖️</p>
                                    @else
                                        <p class="text-4xl mb-2">😔</p>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <p class="text-xs font-bold uppercase" style="color: #7C6FAB; letter-spacing: 1px">
                                        Rewards Earned</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="p-3 rounded-lg text-center"
                                            style="background: #FDF2F8; border: 1px solid #FEE2E2">
                                            <p class="text-xs mb-1" style="color: #7C6FAB">Fans</p>
                                            <p class="text-lg font-bold" style="color: #E879A0">
                                                +{{ number_format($rewardPreview['fans']) }}</p>
                                        </div>
                                        <div class="p-3 rounded-lg text-center"
                                            style="background: #FFFBEB; border: 1px solid #FEF3C7">
                                            <p class="text-xs mb-1" style="color: #7C6FAB">SP</p>
                                            <p class="text-lg font-bold" style="color: #F59E0B">
                                                +{{ $rewardPreview['sp'] }}</p>
                                        </div>
                                        <div class="p-3 rounded-lg text-center"
                                            style="background: #F0FDF4; border: 1px solid #D1FAE5">
                                            <p class="text-xs mb-1" style="color: #7C6FAB">Stats</p>
                                            <p class="text-lg font-bold" style="color: #10B981">
                                                +{{ $rewardPreview['statBonus'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if ($placement > 3)
                                    <div class="p-3 rounded-lg mt-4"
                                        style="background: linear-gradient(135deg,#F5F3FF,#EDE9FE); border: 1px solid #C4B5FD">
                                        <p class="text-xs font-bold mb-2" style="color: #7C3AED">🤖 AI Recovery Advice
                                        </p>
                                        <p class="text-xs" style="color: #7C3AED">Focus on recovery today. Recommend:
                                            Rest facility level 2+, Recovery Skill activation.</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="px-7 py-4 border-t" style="border-color: #EDE9FE">
                    <div class="flex gap-3">
                        @if ($step === 'enter')
                            <button wire:click="closeModal" class="flex-1 px-4 py-3 rounded-lg font-bold transition-all"
                                style="background: transparent; color: #7C6FAB; border: 1px solid #EDE9FE">
                                Cancel
                            </button>
                            <button wire:click="recordResult"
                                class="flex-1 px-4 py-3 rounded-lg font-bold text-white transition-all"
                                @if ($placement === 0) disabled
                                style="background: #D1D5DB; opacity: 0.5"
                            @else
                                style="background: linear-gradient(135deg,#E879A0,#7C3AED); box-shadow: 0 4px 12px rgba(232,121,160,.35)" @endif>
                                Record Result
                            </button>
                        @else
                            @if ($placement <= 3)
                                <button wire:click="closeModal"
                                    class="w-full px-4 py-3 rounded-lg font-bold text-white transition-all"
                                    style="background: linear-gradient(135deg,#F59E0B,#F97316); box-shadow: 0 4px 12px rgba(245,158,11,.35); border-radius: 10px">
                                    🎉 Back to Dashboard
                                </button>
                            @else
                                <button wire:click="closeModal"
                                    class="w-full px-4 py-3 rounded-lg font-bold text-white transition-all"
                                    style="background: linear-gradient(135deg,#E879A0,#7C3AED); box-shadow: 0 4px 12px rgba(232,121,160,.35); border-radius: 10px">
                                    Continue Training
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
