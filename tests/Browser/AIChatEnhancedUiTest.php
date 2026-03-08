<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    Route::middleware('web')->get('/test/browser/ai-components', function () {
        return response()->view('test.ai-browser-components');
    });
});

it('renders the enhanced ai chat controls and keyboard shortcuts modal', function () {
    $this->actingAs($this->user);

    $page = visit('/ai/chat');

    $page->assertSee('AI Career Assistant')
        ->assertPresent('[aria-label="Select AI model"]')
        ->assertPresent('#message-input')
        ->assertPresent('[aria-label="Export conversation"]')
        ->click('[aria-label="Show keyboard shortcuts"]')
        ->assertSee('Keyboard Shortcuts')
        ->assertPresent('[role="dialog"][aria-modal="true"]');

    $page->script("document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }))");

    $page->wait(0.2)
        ->assertScript("(() => { const dialog = document.querySelector('[role=\"dialog\"]'); return dialog === null || window.getComputedStyle(dialog).display === 'none'; })()")
        ->assertNoJavaScriptErrors();
});

it('supports export and delete actions in the chat ui', function () {
    $this->actingAs($this->user);

    $page = visit('/ai/chat');

    $page->script(<<<'JS'
        window.__clipboardValue = null;
        window.__downloadCapture = null;
        window.__copyFallbackCommand = null;
        window.confirm = () => true;

        Object.defineProperty(navigator, 'clipboard', {
            configurable: true,
            value: undefined,
        });

        document.execCommand = (command) => {
            window.__copyFallbackCommand = command;
            window.__clipboardValue = document.activeElement?.value ?? null;

            return command === 'copy';
        };

        URL.createObjectURL = (blob) => {
            window.__downloadBlobType = blob.type;
            return 'blob:test-download';
        };

        HTMLAnchorElement.prototype.click = function () {
            window.__downloadCapture = {
                href: this.href,
                download: this.download,
            };
        };

        const chatRoot = document.querySelector('.ai-chat-interface');
        const chat = Alpine.$data(chatRoot);

        chat.messages = [
            {
                id: 101,
                sender: 'user',
                content: 'How should I train next?',
                timestamp: new Date().toISOString(),
            },
            {
                id: 102,
                sender: 'ai',
                content: 'Focus on stamina this turn.',
                timestamp: new Date().toISOString(),
                metadata: {
                    model: 'llama3.3',
                    provider: 'ollama',
                },
            },
        ];

        chat.saveToLocalStorage();
        window.__startingConversationId = chat.conversationId;
    JS);

    $page->assertSee('How should I train next?')
        ->assertSee('Focus on stamina this turn.')
        ->assertPresent('[aria-label="Message from AI assistant"] [aria-label="Copy message"]')
        ->wait(0.2)
        ->click('[aria-label="Export conversation"]')
        ->wait(0.2)
        ->click('[aria-label="Message from AI assistant"] [aria-label="Delete message"]')
        ->wait(0.2)
        ->assertScript('Alpine.$data(document.querySelector(".ai-chat-interface")).messages.length === 1')
        ->assertNoJavaScriptErrors();
});

it('shows contextual system errors for chat failures', function () {
    $this->actingAs($this->user);

    $page = visit('/ai/chat');

    $page->script(<<<'JS'
        const chat = Alpine.$data(document.querySelector('.ai-chat-interface'));
        chat.showError('Network error. Please check your connection and try again.');
    JS);

    $page->assertSee('Connection Error')
        ->assertSee('Network error. Please check your connection and try again.')
        ->assertScript('(() => { const messages = Alpine.$data(document.querySelector(".ai-chat-interface")).messages; const last = messages[messages.length - 1]; return last.sender === "system" && last.isError === true; })()')
        ->assertNoJavaScriptErrors();
});

it('renders the standalone ai components and dispatches ask-ai events from recommendation cards', function () {
    $this->actingAs($this->user);

    $page = visit('/test/browser/ai-components')->inDarkMode();

    $page->assertSee('Loading Skeleton')
        ->assertSee('Error State')
        ->assertSee('Recommendation Card')
        ->assertPresent('#loading-skeleton-demo [role="status"][aria-label="Loading content"]')
        ->assertPresent('#error-state-demo [role="alert"]')
        ->assertSee('Model unavailable')
        ->assertSee('88%')
        ->assertScript("Array.from(document.querySelectorAll('#recommendation-card-demo [style]')).some((el) => el.getAttribute('style')?.includes('width: 88%'))")
        ->click('Ask AI')
        ->wait(0.2)
        ->assertScript("window.__quickMessage === 'Tell me more about this recommendation: Build stamina before the next long race'")
        ->assertNoJavaScriptErrors();
});
