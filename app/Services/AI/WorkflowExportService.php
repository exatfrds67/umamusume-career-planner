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
    public function exportAsJson(AIConversation $conversation, bool $includeAnalytics = true): array
    {
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
            $workflow = $this->conversationService->exportWorkflow($conversation, 'markdown');
            $html = $this->generatePdfHtml($workflow);

            Log::info('[WorkflowExport] PDF-ready HTML export created', [
                'conversation_id' => $conversation->conversation_id,
            ]);

            return $html;
        } catch (\Exception $e) {
            Log::error('[WorkflowExport] PDF export failed', [
                'conversation_id' => $conversation->conversation_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate print-ready HTML for PDF export
     *
     * @param  array<string, mixed>  $workflow
     */
    protected function generatePdfHtml(array $workflow): string
    {
        $title = isset($workflow['title']) && is_string($workflow['title']) ? e($workflow['title']) : 'Untitled';
        $type = isset($workflow['type']) && is_string($workflow['type']) ? e($workflow['type']) : 'Unknown';
        $createdAt = isset($workflow['created_at']) && is_string($workflow['created_at']) ? e($workflow['created_at']) : 'Unknown';
        $conversationId = isset($workflow['conversation_id']) && is_string($workflow['conversation_id']) ? e($workflow['conversation_id']) : 'Unknown';

        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <title>{$title}</title>
        <style>
        body{font-family:system-ui,-apple-system,sans-serif;max-width:800px;margin:0 auto;padding:2rem;color:#1a1a1a;line-height:1.6}
        h1{border-bottom:2px solid #333;padding-bottom:.5rem}
        h2{color:#333;margin-top:2rem}
        h3{color:#555}
        .meta{color:#666;font-size:.9rem;margin-bottom:1.5rem}
        .message{border:1px solid #ddd;border-radius:8px;padding:1rem;margin:1rem 0;page-break-inside:avoid}
        .message-header{font-weight:bold;margin-bottom:.5rem}
        .message-time{color:#888;font-size:.85rem}
        .tools{background:#f5f5f5;padding:.5rem;border-radius:4px;font-size:.85rem;margin-top:.5rem}
        .analytics{background:#f0f7ff;padding:1rem;border-radius:8px;margin-top:2rem}
        @media print{body{padding:0}@page{margin:2cm}}
        </style>
        </head>
        <body>
        <h1>{$title}</h1>
        <div class="meta">
        <p><strong>Type:</strong> {$type} | <strong>Created:</strong> {$createdAt} | <strong>ID:</strong> {$conversationId}</p>
        </div>
        <h2>Messages</h2>
        HTML;

        $messages = isset($workflow['messages']) && is_array($workflow['messages']) ? $workflow['messages'] : [];
        foreach ($messages as $message) {
            if (! is_array($message)) {
                continue;
            }

            $msgType = isset($message['type']) && is_string($message['type']) ? e(ucfirst($message['type'])) : 'Unknown';
            $agentData = isset($message['agent']) && is_array($message['agent']) ? $message['agent'] : [];
            $agent = isset($agentData['name']) && is_string($agentData['name']) ? e($agentData['name']) : '';
            $time = isset($message['sent_at']) && is_string($message['sent_at']) ? e($message['sent_at']) : '';
            $content = isset($message['content']) && is_string($message['content']) ? e($message['content']) : '';

            $agentLabel = $agent !== '' ? " (Agent: {$agent})" : '';

            $html .= '<div class="message">';
            $html .= "<div class=\"message-header\">{$msgType}{$agentLabel}</div>";
            if ($time !== '') {
                $html .= "<div class=\"message-time\">{$time}</div>";
            }
            $html .= "<p>{$content}</p>";

            $toolsData = isset($message['tools']) && is_array($message['tools']) ? $message['tools'] : [];
            $toolsUsed = isset($toolsData['used']) && is_array($toolsData['used']) ? $toolsData['used'] : [];
            if (! empty($toolsUsed)) {
                $toolNames = [];
                foreach ($toolsUsed as $tool) {
                    if (is_string($tool)) {
                        $toolNames[] = e($tool);
                    }
                }
                $html .= '<div class="tools"><strong>Tools:</strong> '.implode(', ', $toolNames).'</div>';
            }

            $html .= '</div>';
        }

        if (isset($workflow['analytics']) && is_array($workflow['analytics'])) {
            $analytics = $workflow['analytics'];
            $totalMessages = isset($analytics['total_messages']) && is_int($analytics['total_messages']) ? $analytics['total_messages'] : 0;
            $userMessages = isset($analytics['user_messages']) && is_int($analytics['user_messages']) ? $analytics['user_messages'] : 0;
            $aiMessages = isset($analytics['ai_messages']) && is_int($analytics['ai_messages']) ? $analytics['ai_messages'] : 0;

            $html .= '<div class="analytics"><h2>Analytics</h2>';
            $html .= "<p>Total Messages: {$totalMessages} | User: {$userMessages} | AI: {$aiMessages}</p>";
            $html .= '</div>';
        }

        $html .= '</body></html>';

        return $html;
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
                'json' => json_encode($this->exportAsJson($conversation), JSON_PRETTY_PRINT) ?: '',
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
    public function generateShareableLink(AIConversation $conversation, int $expiresInDays = 7): array
    {
        try {
            $filename = $this->saveToFile($conversation, 'json');
            $expiresAt = now()->addDays($expiresInDays);

            // Local disk doesn't support temporaryUrl, use storage path or a download route
            $path = Storage::disk('local')->path("exports/{$filename}");

            Log::info('[WorkflowExport] Shareable link generated', [
                'conversation_id' => $conversation->conversation_id,
                'expires_at' => $expiresAt,
            ]);

            return [
                'path' => $path,
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
     *
     * @param  array<string, mixed>  $workflowData
     */
    public function importFromJson(array $workflowData): AIConversation
    {
        try {
            // Validate workflow data
            $this->validateWorkflowData($workflowData);

            $conversationId = isset($workflowData['conversation_id']) && is_string($workflowData['conversation_id'])
                ? $workflowData['conversation_id']
                : \Illuminate\Support\Str::uuid()->toString();
            $conversationType = isset($workflowData['type']) && is_string($workflowData['type'])
                ? $workflowData['type']
                : 'imported';
            $title = isset($workflowData['title']) && is_string($workflowData['title'])
                ? $workflowData['title'].' (Imported)'
                : 'Imported Workflow';
            $createdAt = isset($workflowData['created_at']) && is_string($workflowData['created_at'])
                ? $workflowData['created_at']
                : now();

            // Create conversation from imported data
            $conversation = AIConversation::create([
                'user_id' => auth()->id(),
                'conversation_id' => $conversationId,
                'conversation_type' => $conversationType,
                'conversation_title' => $title,
                'status' => 'archived',
                'started_at' => $createdAt,
                'ai_model' => 'imported',
                'ai_version' => '1.0',
            ]);

            // Import messages
            $messages = isset($workflowData['messages']) && is_array($workflowData['messages'])
                ? $workflowData['messages']
                : [];

            foreach ($messages as $messageData) {
                if (! is_array($messageData)) {
                    continue;
                }

                $msgType = isset($messageData['type']) && is_string($messageData['type'])
                    ? $messageData['type']
                    : 'message';
                $msgContent = isset($messageData['content']) && is_string($messageData['content'])
                    ? $messageData['content']
                    : '';
                $agentData = isset($messageData['agent']) && is_array($messageData['agent'])
                    ? $messageData['agent']
                    : [];
                $agentId = isset($agentData['id']) && is_string($agentData['id'])
                    ? $agentData['id']
                    : null;
                $agentType = isset($agentData['type']) && is_string($agentData['type'])
                    ? $agentData['type']
                    : null;
                $agentName = isset($agentData['name']) && is_string($agentData['name'])
                    ? $agentData['name']
                    : null;
                $toolsData = isset($messageData['tools']) && is_array($messageData['tools'])
                    ? $messageData['tools']
                    : [];
                /** @var array<int, mixed> */
                $toolsUsed = isset($toolsData['used']) && is_array($toolsData['used'])
                    ? $toolsData['used']
                    : [];
                /** @var array<string, mixed> */
                $toolResults = isset($toolsData['results']) && is_array($toolsData['results'])
                    ? $toolsData['results']
                    : [];

                $this->conversationService->addMessage(
                    $conversation,
                    $msgType,
                    $msgContent,
                    $agentId,
                    $agentType,
                    $agentName,
                    $toolsUsed,
                    $toolResults,
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
        $title = isset($workflow['title']) && is_string($workflow['title']) ? $workflow['title'] : 'Untitled';
        $type = isset($workflow['type']) && is_string($workflow['type']) ? $workflow['type'] : 'Unknown';
        $createdAt = isset($workflow['created_at']) && is_string($workflow['created_at']) ? $workflow['created_at'] : 'Unknown';
        $conversationId = isset($workflow['conversation_id']) && is_string($workflow['conversation_id']) ? $workflow['conversation_id'] : 'Unknown';

        $md = "# {$title}\n\n";
        $md .= "**Type:** {$type}\n";
        $md .= "**Created:** {$createdAt}\n";
        $md .= "**Conversation ID:** {$conversationId}\n\n";

        $md .= "## Messages\n\n";

        $messages = isset($workflow['messages']) && is_array($workflow['messages']) ? $workflow['messages'] : [];
        foreach ($messages as $message) {
            if (! is_array($message)) {
                continue;
            }

            $msgType = isset($message['type']) && is_string($message['type']) ? ucfirst($message['type']) : 'Unknown';
            $agentData = isset($message['agent']) && is_array($message['agent']) ? $message['agent'] : [];
            $agent = isset($agentData['name']) && is_string($agentData['name']) ? $agentData['name'] : 'Unknown';
            $time = isset($message['sent_at']) && is_string($message['sent_at']) ? $message['sent_at'] : 'Unknown';

            $md .= "### {$msgType} Message";
            if (isset($agentData['name']) && is_string($agentData['name'])) {
                $md .= " (Agent: {$agent})";
            }
            $md .= "\n";
            $md .= "*{$time}*\n\n";
            $content = isset($message['content']) && is_string($message['content']) ? $message['content'] : '';
            $md .= "{$content}\n\n";

            $toolsData = isset($message['tools']) && is_array($message['tools']) ? $message['tools'] : [];
            $toolsUsed = isset($toolsData['used']) && is_array($toolsData['used']) ? $toolsData['used'] : [];
            if (! empty($toolsUsed)) {
                $toolNames = [];
                foreach ($toolsUsed as $tool) {
                    if (is_string($tool)) {
                        $toolNames[] = $tool;
                    }
                }
                $md .= '**Tools Used:** '.implode(', ', $toolNames)."\n\n";
            }

            $qualityData = isset($message['quality']) && is_array($message['quality']) ? $message['quality'] : [];
            $rating = isset($qualityData['rating']) ? $qualityData['rating'] : null;
            if ($rating !== null) {
                $ratingStr = is_int($rating) || is_float($rating) ? (string) $rating : '';
                $md .= "**Rating:** {$ratingStr}/5\n\n";
            }

            $md .= "---\n\n";
        }

        if (isset($workflow['analytics']) && is_array($workflow['analytics'])) {
            $analytics = $workflow['analytics'];
            $totalMessages = isset($analytics['total_messages']) && is_int($analytics['total_messages']) ? (string) $analytics['total_messages'] : '0';
            $userMessages = isset($analytics['user_messages']) && is_int($analytics['user_messages']) ? (string) $analytics['user_messages'] : '0';
            $aiMessages = isset($analytics['ai_messages']) && is_int($analytics['ai_messages']) ? (string) $analytics['ai_messages'] : '0';

            $md .= "## Analytics\n\n";
            $md .= "- **Total Messages:** {$totalMessages}\n";
            $md .= "- **User Messages:** {$userMessages}\n";
            $md .= "- **AI Messages:** {$aiMessages}\n\n";

            $agentBreakdown = isset($analytics['agent_breakdown']) && is_array($analytics['agent_breakdown']) ? $analytics['agent_breakdown'] : [];
            if (! empty($agentBreakdown)) {
                $md .= "### Agent Breakdown\n\n";
                foreach ($agentBreakdown as $agentStats) {
                    if (! is_array($agentStats)) {
                        continue;
                    }
                    $agentName = isset($agentStats['agent_name']) && is_string($agentStats['agent_name']) ? $agentStats['agent_name'] : 'Unknown';
                    $msgCount = isset($agentStats['message_count']) && is_int($agentStats['message_count']) ? (string) $agentStats['message_count'] : '0';
                    $avgRating = isset($agentStats['avg_rating']) && (is_float($agentStats['avg_rating']) || is_int($agentStats['avg_rating'])) ? (string) $agentStats['avg_rating'] : '0';
                    $helpfulness = isset($agentStats['helpfulness_rate']) && (is_float($agentStats['helpfulness_rate']) || is_int($agentStats['helpfulness_rate'])) ? (string) $agentStats['helpfulness_rate'] : '0';

                    $md .= "- **{$agentName}**: {$msgCount} messages, ";
                    $md .= "Avg Rating: {$avgRating}, ";
                    $md .= "Helpfulness: {$helpfulness}%\n";
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

        $messages = $data['messages'];
        if (! is_array($messages) || empty($messages)) {
            throw new \InvalidArgumentException('Messages must be a non-empty array');
        }
    }
}
