<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Accessibility Settings Livewire Component
 *
 * Provides toggles for accessibility preferences:
 * reduced motion, high contrast, font size, focus indicators,
 * screen reader optimization, and color blind mode.
 *
 * Preferences are persisted to the user's `accessibility_settings` JSON field.
 *
 * @see Requirements: NFR-A-11, NFR-A-06, NFR-A-03
 */
class AccessibilitySettings extends Component
{
    public bool $reducedMotion = false;

    public bool $highContrast = false;

    public string $fontSize = 'medium';

    public bool $focusIndicators = true;

    public bool $screenReaderOptimization = false;

    public string $colorBlindMode = 'none';

    public bool $saved = false;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user) {
            $settings = $user->accessibility_settings ?? [];

            $this->reducedMotion = (bool) ($settings['reduced_motion'] ?? false);
            $this->highContrast = (bool) ($settings['high_contrast'] ?? false);
            $this->fontSize = (string) ($settings['font_size'] ?? 'medium');
            $this->focusIndicators = (bool) ($settings['focus_indicators'] ?? true);
            $this->screenReaderOptimization = (bool) ($settings['screen_reader_optimization'] ?? false);
            $this->colorBlindMode = (string) ($settings['color_blind_mode'] ?? 'none');
        }
    }

    public function updatedReducedMotion(): void
    {
        $this->saveSettings();
    }

    public function updatedHighContrast(): void
    {
        $this->saveSettings();
    }

    public function updatedFontSize(): void
    {
        $this->saveSettings();
    }

    public function updatedFocusIndicators(): void
    {
        $this->saveSettings();
    }

    public function updatedScreenReaderOptimization(): void
    {
        $this->saveSettings();
    }

    public function updatedColorBlindMode(): void
    {
        $this->saveSettings();
    }

    /**
     * Persist all accessibility settings to the user's profile.
     */
    public function saveSettings(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $user->accessibility_settings = [
            'reduced_motion' => $this->reducedMotion,
            'high_contrast' => $this->highContrast,
            'font_size' => $this->fontSize,
            'focus_indicators' => $this->focusIndicators,
            'screen_reader_optimization' => $this->screenReaderOptimization,
            'color_blind_mode' => $this->colorBlindMode,
        ];

        $user->save();

        $this->saved = true;

        $this->dispatch('accessibility-updated', settings: $user->accessibility_settings);
    }

    /**
     * Reset all accessibility settings to defaults.
     */
    public function resetToDefaults(): void
    {
        $this->reducedMotion = false;
        $this->highContrast = false;
        $this->fontSize = 'medium';
        $this->focusIndicators = true;
        $this->screenReaderOptimization = false;
        $this->colorBlindMode = 'none';

        $this->saveSettings();
    }

    public function render(): View
    {
        return view('livewire.settings.accessibility-settings');
    }
}
