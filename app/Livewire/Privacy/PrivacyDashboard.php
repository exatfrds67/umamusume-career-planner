<?php

declare(strict_types=1);

namespace App\Livewire\Privacy;

use App\Enums\ConsentType;
use App\Enums\DeletionStatus;
use App\Models\DeletionRequest;
use App\Services\Privacy\ConsentManagementService;
use App\Services\Privacy\DataDeletionService;
use App\Services\Privacy\DataExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class PrivacyDashboard extends Component
{
    /** @var array<string, bool> */
    public array $consents = [];

    public string $deletionReason = '';

    public bool $showDeletionConfirm = false;

    public bool $exportReady = false;

    public string $exportPath = '';

    public ?string $statusMessage = null;

    public ?string $statusType = null;

    public function mount(): void
    {
        $consentService = app(ConsentManagementService::class);
        $this->consents = $consentService->getConsentStatus(auth()->user());
    }

    /**
     * Toggle a specific consent setting.
     */
    public function toggleConsent(string $consentType): void
    {
        $type = ConsentType::tryFrom($consentType);
        if ($type === null) {
            return;
        }

        $consentService = app(ConsentManagementService::class);
        $user = auth()->user();

        $currentValue = $this->consents[$consentType] ?? false;

        if ($currentValue) {
            $consentService->revokeConsent($user, $type, request());
            $this->consents[$consentType] = false;
        } else {
            $consentService->grantConsent($user, $type, request());
            $this->consents[$consentType] = true;
        }

        $this->setStatus('Consent updated successfully.', 'success');
    }

    /**
     * Generate a personal data export.
     */
    public function exportData(): void
    {
        $exportService = app(DataExportService::class);
        $this->exportPath = $exportService->generateExportFile(auth()->user());
        $this->exportReady = true;
        $this->setStatus('Your data export is ready for download.', 'success');
    }

    /**
     * Download the generated export file.
     */
    public function downloadExport(): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! $this->exportPath || ! Storage::exists($this->exportPath)) {
            $this->setStatus('Export file not found. Please generate a new export.', 'error');

            return null;
        }

        return Storage::download($this->exportPath, 'my-data-export.json');
    }

    /**
     * Show the deletion confirmation dialog.
     */
    public function confirmDeletion(): void
    {
        $this->showDeletionConfirm = true;
    }

    /**
     * Cancel the deletion confirmation dialog.
     */
    public function cancelDeletionConfirm(): void
    {
        $this->showDeletionConfirm = false;
        $this->deletionReason = '';
    }

    /**
     * Submit a deletion request.
     */
    public function requestDeletion(): void
    {
        $deletionService = app(DataDeletionService::class);
        $user = auth()->user();

        if ($deletionService->hasPendingDeletion($user)) {
            $this->setStatus('You already have a pending deletion request.', 'warning');
            $this->showDeletionConfirm = false;

            return;
        }

        $deletionService->requestDeletion($user, $this->deletionReason ?: null);

        $this->showDeletionConfirm = false;
        $this->deletionReason = '';
        $this->setStatus('Deletion request submitted. You have 30 days to cancel.', 'success');
    }

    /**
     * Cancel a pending deletion request.
     */
    public function cancelDeletion(): void
    {
        $deletionService = app(DataDeletionService::class);
        $user = auth()->user();

        $request = $deletionService->getActiveDeletionRequest($user);

        if (! $request) {
            $this->setStatus('No pending deletion request found.', 'error');

            return;
        }

        $deletionService->cancelDeletion($request);
        $this->setStatus('Deletion request cancelled successfully.', 'success');
    }

    public function render(): View
    {
        $user = auth()->user();
        $deletionService = app(DataDeletionService::class);

        $activeDeletion = $deletionService->getActiveDeletionRequest($user);
        $deletionHistory = DeletionRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [DeletionStatus::Cancelled, DeletionStatus::Completed])
            ->latest()
            ->limit(5)
            ->get();

        $consentTypes = ConsentType::cases();

        return view('privacy.dashboard', [
            'activeDeletion' => $activeDeletion,
            'deletionHistory' => $deletionHistory,
            'consentTypes' => $consentTypes,
        ]);
    }

    private function setStatus(string $message, string $type): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
    }
}
