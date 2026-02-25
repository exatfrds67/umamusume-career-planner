/**
 * Predictions Grid
 * Provides facility and training selection handlers for the predictions grid partial.
 */

window.selectFacility = function (facility) {
    document.querySelectorAll(".training-facility").forEach((el) => {
        el.classList.remove("ring-2", "ring-primary-500");
    });
    document
        .querySelector(`[data-facility="${facility}"]`)
        ?.classList.add("ring-2", "ring-primary-500");
};

window.selectTraining = function (facility) {
    window.dispatchEvent(
        new CustomEvent("toast", {
            detail: {
                type: "info",
                message: `Training selection: ${facility.charAt(0).toUpperCase() + facility.slice(1)}`,
            },
        }),
    );
};
