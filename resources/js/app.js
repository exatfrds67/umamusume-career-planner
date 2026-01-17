import "./bootstrap";
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";

// Import character validation module
import "./character-validation.js";

// Import training predictions module
import "./training-predictions.js";

// Import settings module
import "./settings.js";

// Import core modules
import "./core/EventBus.js";
import "./core/ResponsiveSystem.js";
import "./core/AccessibilitySettings.js";
import "./core/AccessibilitySystem.js";
import "./core/ThemeSystem.js";

// Register Alpine plugins
Alpine.plugin(persist);

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();
