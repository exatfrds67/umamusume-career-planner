/**
 * Profile Management Page
 * Handles tab navigation, avatar upload, and profile settings
 */

// Access data from data island (injected by Blade)
const profileDataElement = document.getElementById("profile-data");
const profileData = profileDataElement
    ? JSON.parse(profileDataElement.textContent)
    : { routes: {} };

// Uses window.Alpine set by app.js (Livewire-bundled Alpine instance)
// Register Alpine components on initialization
document.addEventListener("alpine:init", () => {
    // Main profile manager component
    Alpine.data("profileManager", () => ({
        activeTab: "account",
        loading: false,
        tabs: ["account", "preferences", "notifications", "privacy", "security"],

        init() {
            // Check URL hash for tab
            const hash = window.location.hash.replace("#", "");
            if (hash && this.tabs.includes(hash)) {
                this.activeTab = hash;
            }

            // Update URL hash when tab changes
            this.$watch("activeTab", (value) => {
                window.location.hash = value;
            });
        },

        focusNextTab() {
            const idx = this.tabs.indexOf(this.activeTab);
            const next = this.tabs[(idx + 1) % this.tabs.length];
            this.activeTab = next;
            this.$nextTick(() => {
                const el = document.getElementById("tab-" + next);
                if (el) el.focus();
            });
        },

        focusPrevTab() {
            const idx = this.tabs.indexOf(this.activeTab);
            const prev = this.tabs[(idx - 1 + this.tabs.length) % this.tabs.length];
            this.activeTab = prev;
            this.$nextTick(() => {
                const el = document.getElementById("tab-" + prev);
                if (el) el.focus();
            });
        },

        focusFirstTab() {
            this.activeTab = this.tabs[0];
            this.$nextTick(() => {
                const el = document.getElementById("tab-" + this.tabs[0]);
                if (el) el.focus();
            });
        },

        focusLastTab() {
            const last = this.tabs[this.tabs.length - 1];
            this.activeTab = last;
            this.$nextTick(() => {
                const el = document.getElementById("tab-" + last);
                if (el) el.focus();
            });
        },
    }));

    // Avatar uploader component
    Alpine.data("avatarUploader", () => ({
        previewUrl: null,
        uploading: false,
        error: null,
        success: null,

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Reset messages
            this.error = null;
            this.success = null;

            // Validate file type
            const validTypes = [
                "image/jpeg",
                "image/png",
                "image/jpg",
                "image/gif",
            ];
            if (!validTypes.includes(file.type)) {
                this.error =
                    "Please select a valid image file (JPG, PNG, or GIF).";
                return;
            }

            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                this.error = "File size must be less than 2MB.";
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
            };
            reader.readAsDataURL(file);

            // Upload file
            this.uploadAvatar(file);
        },

        async uploadAvatar(file) {
            this.uploading = true;
            this.error = null;

            const formData = new FormData();
            formData.append("avatar", file);

            try {
                const response = await fetch(profileData.routes.avatarUpload, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Upload failed");
                }

                this.success = "Avatar updated successfully!";

                // Update preview with server URL if available
                if (data.avatar_url) {
                    this.previewUrl = data.avatar_url;
                }

                // Clear success message after 3 seconds
                setTimeout(() => {
                    this.success = null;
                }, 3000);
            } catch (error) {
                this.error =
                    error.message ||
                    "Failed to upload avatar. Please try again.";
                this.previewUrl = null;
            } finally {
                this.uploading = false;
                // Reset file input
                this.$refs.fileInput.value = "";
            }
        },

        async removeAvatar() {
            if (!confirm("Are you sure you want to remove your avatar?")) {
                return;
            }

            this.uploading = true;
            this.error = null;

            try {
                const response = await fetch(profileData.routes.avatarDelete, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Failed to remove avatar");
                }

                this.previewUrl = null;
                this.success = "Avatar removed successfully!";

                // Clear success message after 3 seconds
                setTimeout(() => {
                    this.success = null;
                }, 3000);
            } catch (error) {
                this.error =
                    error.message ||
                    "Failed to remove avatar. Please try again.";
            } finally {
                this.uploading = false;
            }
        },
    }));
});

// Make avatarUploader globally available for backward compatibility
window.avatarUploader = function () {
    return Alpine.data("avatarUploader")();
};
