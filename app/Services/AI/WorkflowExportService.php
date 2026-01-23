<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AIConversation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Workflow Export Service
 *
 * Handles exporting conversation workflows in various formats
 * for sharing complex strategies and analysis.
 *
 * Requirements: 13.4, 56.4
 */
class WorkflowExportService
{
    public function __construct(
        protected ConversationManagementService $conversationService
    ) {}

    /**
     * Export workflow as JSON
     *
     * @return array<string, mixed>
     */
    public function exportAsJson(): array
        try {
            $workflow = $this->conversationService->exportWorkflow($conversation, 'json');

            if (! $includeAnalytics) {
                unset($workflow['analytics']);
            }

            Log::info('[WorkflowExport] JSON export created', [
                'conversation_id' => $conversation->conversation_id,
            ]);

            return $workflow;
        } catch (\Exception $e) {
            Log::error('[WorkflowExport] JSON export failed', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Export workflow as Markdown
     */
    public function exportAsMarkdown(AIConversation $conversation): string
    {
        try {
            $workflow = $this->conversationService->exportWorkflow($conversation, 'markdown');
            $markdown = $this->generateMarkdown($workflow);

            Log::info('[WorkflowExport] Markdown export created', [
                'conversation_id' => $conversation->conversation_id,
            ]);

            return $markdown;
        } catch (\Exception $e) {
            Log::error('[WorkflowExport] Markdown export failed', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Export workflow as PDF (placeholder - would require PDF library)
     */
    public function exportAsPdf(AIConversation $conversation): string
    {
        try {
            // This would use a PDF library like dompdf or snappy
            // For now, return a placeholder
            $markdown = $this->exportAsMarkdown($conversation);

            Log::info('[WorkflowExport] PDF export requested', [
                'conversation_id' => $conversation->conversation_id,
                'note' => 'PDF generation requires additional library',
            ]);

            return $markdown; // Would convert to PDF
        } catch (\Exception $e) {
            Log::error('[WorkflowExport] PDF export failed', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Save workflow to file
     */
    public function saveToFile(
        AIConversation $conversation,
        string $format = 'json',
        ?string $filename = null
    ): string {
        try {
            $filename = $filename ?? $this->generateFilename($conversation, $format);

            $content = match ($format) {
                'json' => json_encode($this->exportAsJson($conversation), JSON_PRETTY_PRINT),
                'markdown', 'md' => $this->exportAsMarkdown($conversation),
                'pdf' => $this->exportAsPdf($conversation),
                default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
            };

            Storage::disk('local')->put("exports/{$filename}", $content);

            Log::info('[WorkflowExport] Workflow saved to file', [
                'conversation_id' => $conversation->conversation_id,
                'filename' => $filename,
                'format' => $format,
            ]);

            return $filename;
        } catch (\Exception $e) {
            Log::error('[WorkflowExport] Failed to save workflow', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate shareable link for workflow
     *
     * @return array<string, mixed>
     */
    public function generateShareableLink(): array
        try {
            $filename = $this->saveToFile($conversation, 'json');
            $expiresAt = now()->addDays($expiresInDays);

            // Generate a temporary signed URL
            $url = Storage::disk('local')->temporaryUrl(
                "exports/{$filename}",
                $expiresAt
            );

            Log::info('[WorkflowExport] Shareable link generated', [
                'conversation_id' => $conversation->conversation_id,
                'expires_at' => $expiresAt,
            ]);

            return [
                'url' => $url,
                'filename' => $filename,
                'expires_at' => $expiresAt->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('[WorkflowExport] Failed to generate shareable link', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Import workflow from JSON
     */
    public function importFromJson(array $workflowData): AIConversation
    {
        try {
            // Validate workflow data
            $this->validateWorkflowData($workflowData);

            // Create conversation from imported data
            $conversation = AIConversation::create([
                'user_id' => auth()->id(),
                'conversation_id' => $workflowData['conversation_id'] ?? \Illuminate\Support\Str::uuid()->toString(),
                'conversation_type' => $workflowData['type'],
                'conversation_title' => $workflowData['title'].' (Imported)',
                'status' => 'archived',
                'started_at' => $workflowData['created_at'] ?? now(),
                'ai_model' => 'imported',
                'ai_version' => '1.0',
            ]);

            // Import messages
            foreach ($workflowData['messages'] as $messageData) {
                $this->conversationService->addMessage(
                    $conversation,
                    $messageData['type'],
                    $messageData['content'],
                    $messageData['agent']['id'] ?? null,
                    $messageData['agent']['type'] ?? null,
                    $messageData['agent']['name'] ?? null,
                    $messageData['tools']['used'] ?? [],
                    $messageData['tools']['results'] ?? [],
                    []
                );
            }

            Log::info('[WorkflowExport] Workflow imported', [
                'conversation_id' => $conversation->conversation_id,
            ]);

            return $conversation;
        } catch (\Exception $e) {
            Log::error('[WorkflowExport] Failed to import workflow', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Protected helper methods
     *
     * @param  array<string, mixed>  $workflow
     */
    protected function generateMarkdown(array $workflow): string
    {
        $md = "# {$workflow['title']}\n\n";
        $md .= "**Type:** {$workflow['type']}\n";
        $md .= "**Created:** {$workflow['created_at']}\n";
        $md .= "**Conversation ID:** {$workflow['conversation_id']}\n\n";

        $md .= "## Messages\n\n";

        foreach ($workflow['messages'] as $message) {
            $type = ucfirst($message['type']);
            $agent = $message['agent']['name'] ?? 'Unknown';
            $time = $message['sent_at'];

            $md .= "### {$type} Message";
            if ($message['agent']['name']) {
                $md .= " (Agent: {$agent})";
            }
            $md .= "\n";
            $md .= "*{$time}*\n\n";
            $md .= "{$message['content']}\n\n";

            if (isset($message['tools']) && ! empty($message['tools']['used'])) {
                $md .= '**Tools Used:** '.implode(', ', $message['tools']['used'])."\n\n";
            }

            if ($message['quality']['rating']) {
                $md .= "**Rating:** {$message['quality']['rating']}/5\n\n";
            }

            $md .= "---\n\n";
        }

        if (isset($workflow['analytics'])) {
            $md .= "## Analytics\n\n";
            $md .= "- **Total Messages:** {$workflow['analytics']['total_messages']}\n";
            $md .= "- **User Messages:** {$workflow['analytics']['user_messages']}\n";
            $md .= "- **AI Messages:** {$workflow['analytics']['ai_messages']}\n\n";

            if (! empty($workflow['analytics']['agent_breakdown'])) {
                $md .= "### Agent Breakdown\n\n";
                foreach ($workflow['analytics']['agent_breakdown'] as $agent) {
                    $md .= "- **{$agent['agent_name']}**: {$agent['message_count']} messages, ";
                    $md .= "Avg Rating: {$agent['avg_rating']}, ";
                    $md .= "Helpfulness: {$agent['helpfulness_rate']}%\n";
                }
                $md .= "\n";
            }
        }

        return $md;
    }

    protected function generateFilename(AIConversation $conversation, string $format): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $title = \Illuminate\Support\Str::slug($conversation->conversation_title ?? 'conversation');

        return "{$title}_{$timestamp}.{$format}";
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function validateWorkflowData(array $data): void
    {
        $required = ['conversation_id', 'title', 'type', 'messages'];

        foreach ($required as $field) {
            if (! isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        if (! is_array((is_array($data) && isset($data['messages']) ? $data['messages'] : null)) || empty((is_array($data) && isset($data['messages']) ? $data['messages'] : null))) {
            throw new \InvalidArgumentException('Messages must be a non-empty array');
        }
    }
}
