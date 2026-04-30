@extends('layouts.app')

@section('title', 'AI Advisor')

@section('content')
{{-- Phase 7: AI Advisor Full Chat Interface --}}
{{-- Design: 260px left sidebar + flex chat area --}}

<style>
@keyframes pulse {
  from { opacity: .4; transform: scale(.85); }
  to   { opacity: 1;  transform: scale(1);   }
}
@keyframes slide {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(200%); }
}
.ai-streaming-cursor {
  display: inline-block;
  width: 2px;
  height: 14px;
  background: #7C3AED;
  vertical-align: middle;
  animation: pulse .6s ease-in-out infinite alternate;
}
</style>

<div
  x-data="{
    activeTab: 'chat',
    selectedModel: 'auto',
    isStreaming: false,
    fillMessage: function(text) {
      window.dispatchEvent(new CustomEvent('ai-fill-message', { detail: { text: text } }));
      this.activeTab = 'chat';
    }
  }"
  data-testid="ai-chat-page"
  style="font-family:'Nunito',sans-serif; background:#F9F5FF; min-height:calc(100vh - 64px); padding:20px;"
>

  {{-- Page Header --}}
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <div>
      <h1 style="font-size:22px; font-weight:900; color:#1E1033; margin:0 0 4px 0;">🤖 AI Advisor</h1>
      <p style="font-size:13px; color:#7C6FAB; margin:0;">
        Powered by Hybrid AI ·
        @if($storageMode->value === 'account')
          <span style="display:inline-flex; align-items:center; gap:4px;">
            <span style="width:8px; height:8px; border-radius:50%; background:#10B981; display:inline-block;"></span>
            Account Mode
          </span>
        @else
          <span style="display:inline-flex; align-items:center; gap:4px;">
            <span style="width:8px; height:8px; border-radius:50%; background:#F59E0B; display:inline-block;"></span>
            Local Mode
          </span>
        @endif
      </p>
    </div>
    <a href="{{ route('characters.index') }}"
       style="font-size:13px; font-weight:700; color:#7C3AED; text-decoration:none; padding:8px 16px; background:#EDE9FE; border-radius:10px; transition:filter .15s;"
       onmouseover="this.style.filter='brightness(1.06)'" onmouseout="this.style.filter='none'">
      👤 Characters
    </a>
  </div>

  {{-- Main Layout: Sidebar + Chat --}}
  <div style="display:flex; gap:20px; height:calc(100vh - 180px);">

    {{-- ===== LEFT SIDEBAR ===== --}}
    <div
      data-testid="ai-context-sidebar"
      style="width:260px; flex-shrink:0; display:flex; flex-direction:column; gap:14px; overflow-y:auto;"
    >

      {{-- Card 1: Active Context --}}
      <div style="background:#fff; border-radius:16px; border:1px solid #EDE9FE; box-shadow:0 2px 12px rgba(124,58,237,0.07); padding:16px;">
        <div style="font-size:12px; font-weight:800; color:#7C6FAB; letter-spacing:1px; text-transform:uppercase; margin-bottom:12px;">
          ACTIVE CONTEXT
        </div>

        @if($character)
          {{-- Character Pill --}}
          <div style="background:#F9F5FF; border-radius:10px; padding:10px; margin-bottom:12px; display:flex; align-items:center; gap:10px;">
            <div style="width:32px; height:32px; border-radius:8px; background:linear-gradient(135deg,#E879A0,#7C3AED); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
              🏇
            </div>
            <div style="min-width:0;">
              <div style="font-size:13px; font-weight:800; color:#1E1033; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ $character->name }}
              </div>
              <div style="font-size:11px; color:#7C6FAB;">
                Turn {{ $character->current_turn ?? 0 }} · {{ ucfirst($character->career_stage ?? 'Junior') }}
              </div>
            </div>
          </div>

          {{-- Stats --}}
          @php
            $stats = $character->current_stats ?? [];
            $statConfig = [
              'speed'   => ['icon' => '⚡', 'label' => 'Speed',   'color' => '#E879A0'],
              'stamina' => ['icon' => '🌿', 'label' => 'Stamina', 'color' => '#10B981'],
              'power'   => ['icon' => '🔥', 'label' => 'Power',   'color' => '#F59E0B'],
              'guts'    => ['icon' => '❤️', 'label' => 'Guts',    'color' => '#EF4444'],
              'wit'     => ['icon' => '💙', 'label' => 'Wit',     'color' => '#3B82F6'],
            ];
            $gradeFor = function(int $v): string {
              return match(true) {
                $v >= 1000 => 'S', $v >= 800 => 'A', $v >= 600 => 'B',
                $v >= 400 => 'C', $v >= 200 => 'D', $v >= 100 => 'E', default => 'F'
              };
            };
            $gradeColor = function(string $g): string {
              return match($g) {
                'S' => '#F59E0B', 'A' => '#E879A0', 'B' => '#7C3AED',
                'C' => '#3B82F6', 'D' => '#6B7280', 'E' => '#9CA3AF', default => '#D1D5DB'
              };
            };
          @endphp
          <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:12px;">
            @foreach($statConfig as $key => $cfg)
              @php $val = (int)($stats[$key] ?? 0); $grade = $gradeFor($val); @endphp
              <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#7C6FAB;">
                  <span>{{ $cfg['icon'] }}</span>
                  <span>{{ $cfg['label'] }}</span>
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                  <span style="font-size:12px; font-weight:800; color:{{ $cfg['color'] }};">{{ $val }}</span>
                  <span style="font-size:10px; font-weight:800; color:#fff; background:{{ $gradeColor($grade) }}; border-radius:4px; padding:1px 6px; letter-spacing:0.5px;">{{ $grade }}</span>
                </div>
              </div>
            @endforeach
          </div>

          {{-- SP + Storage Mode --}}
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
            <div style="background:#FEF3C7; border-radius:10px; padding:8px; text-align:center;">
              <div style="font-size:10px; font-weight:700; color:#92400E; margin-bottom:2px;">SP</div>
              <div style="font-size:15px; font-weight:900; color:#F59E0B;">{{ $character->available_sp ?? 0 }}</div>
            </div>
            <div style="background:{{ $storageMode->value === 'account' ? '#EDE9FE' : '#FFFBEB' }}; border-radius:10px; padding:8px; text-align:center;">
              <div style="font-size:10px; font-weight:700; color:{{ $storageMode->value === 'account' ? '#5B21B6' : '#92400E' }}; margin-bottom:2px;">MODE</div>
              <div style="font-size:11px; font-weight:800; color:{{ $storageMode->value === 'account' ? '#7C3AED' : '#F59E0B' }};">
                {{ $storageMode->value === 'account' ? '🟢 Account' : '🟠 Local' }}
              </div>
            </div>
          </div>

        @else
          {{-- No character state --}}
          <div style="text-align:center; padding:16px 0;">
            <div style="font-size:32px; margin-bottom:8px;">🏇</div>
            <div style="font-size:13px; color:#7C6FAB; margin-bottom:12px;">No active character</div>
            <a href="{{ route('characters.index') }}"
               style="font-size:12px; font-weight:700; color:#fff; background:linear-gradient(135deg,#E879A0,#7C3AED); border-radius:8px; padding:6px 14px; text-decoration:none; display:inline-block;">
              Select Character
            </a>
          </div>
        @endif
      </div>

      {{-- Card 2: Model Selector --}}
      <div
        data-testid="ai-model-selector"
        style="background:#fff; border-radius:16px; border:1px solid #EDE9FE; box-shadow:0 2px 12px rgba(124,58,237,0.07); padding:16px;"
      >
        <div style="font-size:12px; font-weight:800; color:#7C6FAB; letter-spacing:1px; text-transform:uppercase; margin-bottom:12px;">
          AI MODEL
        </div>

        {{-- AWS Bedrock option --}}
        <div
          @click="selectedModel = 'bedrock'"
          :style="selectedModel === 'bedrock' ? 'background:#F5F3FF; border:1px solid #C4B5FD;' : 'background:#F9F5FF; border:1px solid #EDE9FE;'"
          style="border-radius:10px; padding:10px 12px; margin-bottom:8px; cursor:pointer; transition:all .15s;"
        >
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:16px;">☁️</span>
              <div>
                <div style="font-size:12px; font-weight:700; color:#1E1033;">AWS Bedrock Claude</div>
                <div style="font-size:10px; color:#7C6FAB;">Complex strategy queries</div>
              </div>
            </div>
            <div
              :style="selectedModel === 'bedrock' ? 'background:#10B981;' : 'background:#D1D5DB;'"
              style="width:8px; height:8px; border-radius:50%; flex-shrink:0;"
            ></div>
          </div>
        </div>

        {{-- Ollama option --}}
        <div
          @click="selectedModel = 'ollama'"
          :style="selectedModel === 'ollama' ? 'background:#F5F3FF; border:1px solid #C4B5FD;' : 'background:#F9F5FF; border:1px solid #EDE9FE;'"
          style="border-radius:10px; padding:10px 12px; margin-bottom:8px; cursor:pointer; transition:all .15s;"
        >
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:16px;">💻</span>
              <div>
                <div style="font-size:12px; font-weight:700; color:#1E1033;">Local Ollama</div>
                <div style="font-size:10px; color:#7C6FAB;">Fast, private, offline</div>
              </div>
            </div>
            <div
              :style="selectedModel === 'ollama' ? 'background:#10B981;' : 'background:#D1D5DB;'"
              style="width:8px; height:8px; border-radius:50%; flex-shrink:0;"
            ></div>
          </div>
        </div>

        {{-- Auto option --}}
        <div
          @click="selectedModel = 'auto'"
          :style="selectedModel === 'auto' ? 'background:#F5F3FF; border:1px solid #C4B5FD;' : 'background:#F9F5FF; border:1px solid #EDE9FE;'"
          style="border-radius:10px; padding:10px 12px; margin-bottom:10px; cursor:pointer; transition:all .15s;"
        >
          <div style="display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:16px;">🔀</span>
              <div>
                <div style="font-size:12px; font-weight:700; color:#1E1033;">Auto Route</div>
                <div style="font-size:10px; color:#7C6FAB;">Best model per query</div>
              </div>
            </div>
            <div
              :style="selectedModel === 'auto' ? 'background:#10B981;' : 'background:#D1D5DB;'"
              style="width:8px; height:8px; border-radius:50%; flex-shrink:0;"
            ></div>
          </div>
        </div>

        <div style="background:#F9F5FF; border-radius:8px; padding:8px 10px; font-size:11px; color:#7C6FAB; line-height:1.5;">
          Complex queries → Bedrock · Simple → Ollama (auto-routed)
        </div>
      </div>

      {{-- Card 3: Suggested Prompts --}}
      <div
        data-testid="ai-suggested-prompts"
        style="background:#fff; border-radius:16px; border:1px solid #EDE9FE; box-shadow:0 2px 12px rgba(124,58,237,0.07); padding:16px; flex:1;"
      >
        <div style="font-size:12px; font-weight:800; color:#7C6FAB; letter-spacing:1px; text-transform:uppercase; margin-bottom:12px;">
          SUGGESTED
        </div>
        <div style="display:flex; flex-direction:column; gap:8px;">
          @php
            $sp = $character->available_sp ?? 0;
            $nextRace = 'next race';
            $prompts = [
              'What should I train next turn?',
              'Optimize my support deck for ' . $nextRace,
              'Which skills should I prioritize with ' . $sp . ' SP?',
              'Am I on track for URA Finale?',
            ];
          @endphp
          @foreach($prompts as $prompt)
            <button
              @click="fillMessage('{{ addslashes($prompt) }}')"
              :disabled="isStreaming"
              :style="isStreaming ? 'opacity:0.5; cursor:not-allowed;' : 'opacity:1; cursor:pointer;'"
              style="display:block; width:100%; text-align:left; padding:8px 10px; border:1px solid #EDE9FE; background:#F9F5FF; color:#7C3AED; font-size:12px; font-weight:600; line-height:1.4; border-radius:8px; font-family:'Nunito',sans-serif; transition:filter .15s;"
              onmouseover="if(!this.disabled) this.style.filter='brightness(1.06)'"
              onmouseout="this.style.filter='none'"
            >
              {{ $prompt }}
            </button>
          @endforeach
        </div>
      </div>

    </div>
    {{-- ===== END LEFT SIDEBAR ===== --}}

    {{-- ===== RIGHT CHAT CARD ===== --}}
    <div
      style="flex:1; background:#fff; border-radius:16px; border:1px solid #EDE9FE; box-shadow:0 2px 12px rgba(124,58,237,0.07); display:flex; flex-direction:column; overflow:hidden;"
      role="region"
      aria-label="AI Chat Interface"
    >

      {{-- Tab Bar --}}
      <div
        data-testid="ai-chat-tabs"
        style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #EDE9FE; padding:0 20px; flex-shrink:0;"
      >
        <div style="display:flex; gap:0;">
          <button
            data-testid="ai-chat-tab-chat"
            @click="activeTab = 'chat'"
            :style="activeTab === 'chat' ? 'border-bottom:2px solid #E879A0; color:#E879A0; font-weight:700;' : 'border-bottom:2px solid transparent; color:#7C6FAB; font-weight:600;'"
            style="padding:14px 16px; font-size:13px; font-family:'Nunito',sans-serif; background:none; border:none; border-top:none; border-left:none; border-right:none; cursor:pointer; transition:color .15s;"
            aria-selected="true"
            role="tab"
          >
            💬 Chat
          </button>
          <button
            data-testid="ai-chat-tab-history"
            @click="activeTab = 'history'"
            :style="activeTab === 'history' ? 'border-bottom:2px solid #E879A0; color:#E879A0; font-weight:700;' : 'border-bottom:2px solid transparent; color:#7C6FAB; font-weight:600;'"
            style="padding:14px 16px; font-size:13px; font-family:'Nunito',sans-serif; background:none; border:none; border-top:none; border-left:none; border-right:none; cursor:pointer; transition:color .15s;"
            role="tab"
          >
            🕒 History
          </button>
        </div>

        {{-- Status indicator --}}
        <div style="display:flex; align-items:center; gap:6px;">
          <div
            :style="isStreaming ? 'background:#F59E0B;' : 'background:#10B981;'"
            style="width:8px; height:8px; border-radius:50%;"
          ></div>
          <span
            :style="isStreaming ? 'color:#F59E0B;' : 'color:#7C6FAB;'"
            style="font-size:11px; font-weight:600;"
            x-text="isStreaming ? 'Streaming…' : 'Online'"
          ></span>
          <span style="font-size:11px; color:#7C6FAB; margin-left:4px;">
            · {{ $aiStatus['ollama_model'] ?? 'llama3.2' }}
          </span>
        </div>
      </div>

      {{-- Chat Tab Content --}}
      <div x-show="activeTab === 'chat'" style="flex:1; display:flex; flex-direction:column; overflow:hidden; min-height:0;">
        <x-ai.chat-interface
          :character-id="$character?->id"
          :career-id="$character?->currentCareer?->id"
          style="flex:1; min-height:0;"
        />
      </div>

      {{-- History Tab Content --}}
      <div
        x-show="activeTab === 'history'"
        data-testid="ai-history-list"
        style="flex:1; overflow-y:auto; padding:20px;"
      >
        @if(count($recentConversations) > 0)
          <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($recentConversations as $conv)
              @php
                $convId = $conv['conversation_id'] ?? '';
                $model = $conv['ai_model_used'] ?? 'unknown';
                $content = $conv['message_content'] ?? '';
                $createdAt = $conv['created_at'] ?? '';
                $preview = mb_strimwidth(strip_tags((string)$content), 0, 80, '…');
                $dateLabel = $createdAt ? \Carbon\Carbon::parse($createdAt)->diffForHumans() : '';
              @endphp
              <div style="background:#F9F5FF; border:1px solid #EDE9FE; border-radius:12px; padding:14px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <div style="flex:1; min-width:0;">
                  <div style="font-size:12px; font-weight:700; color:#1E1033; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ $preview ?: 'Conversation' }}
                  </div>
                  <div style="font-size:11px; color:#7C6FAB; display:flex; gap:8px;">
                    <span>{{ $dateLabel }}</span>
                    @if($model !== 'unknown')
                      <span>· {{ $model }}</span>
                    @endif
                  </div>
                </div>
                <button
                  @click="activeTab = 'chat'; $nextTick(() => window.dispatchEvent(new CustomEvent('ai-resume-conversation', { detail: { conversationId: '{{ $convId }}' } })))"
                  style="font-size:12px; font-weight:700; color:#fff; background:linear-gradient(135deg,#E879A0,#7C3AED); border:none; border-radius:8px; padding:6px 14px; cursor:pointer; font-family:'Nunito',sans-serif; flex-shrink:0; transition:filter .15s;"
                  onmouseover="this.style.filter='brightness(1.06)'" onmouseout="this.style.filter='none'"
                >
                  Resume
                </button>
              </div>
            @endforeach
          </div>
        @else
          <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; text-align:center; padding:40px;">
            <div style="font-size:48px; margin-bottom:16px;">🕒</div>
            <div style="font-size:15px; font-weight:800; color:#1E1033; margin-bottom:8px;">No conversation history</div>
            <div style="font-size:13px; color:#7C6FAB;">Start a chat to see your history here.</div>
          </div>
        @endif
      </div>

    </div>
    {{-- ===== END RIGHT CHAT CARD ===== --}}

  </div>
</div>

<script>
// Handle suggested prompt fill — bridge to the existing x-ai.chat-interface Alpine component
window.addEventListener('ai-fill-message', function(e) {
  var text = e.detail && e.detail.text ? e.detail.text : '';
  if (!text) return;
  // Try to find the chat textarea and fill it
  var textarea = document.querySelector('.ai-chat-interface textarea, [x-ref="messageInput"], textarea[placeholder*="message"], textarea[placeholder*="ask"], textarea[placeholder*="Ask"]');
  if (textarea) {
    // Alpine.js: dispatch input event to update x-model
    var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, 'value').set;
    nativeInputValueSetter.call(textarea, text);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
  }
  // Also try the sendQuickMessage global if it exists
  if (typeof window.sendQuickMessage === 'function') {
    window.sendQuickMessage(text);
  }
});
</script>

@vite(['resources/js/pages/ai/chat.js'])
@endsection
