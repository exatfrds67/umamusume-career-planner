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
    if (typeof window.openTrainingConfirmation === "function") {
        window.openTrainingConfirmation(facility);
        return;
    }

    window.dispatchEvent(
        new CustomEvent("toast", {
            detail: {
                type: "warning",
                message: "Training confirmation is not ready yet.",
            },
        }),
    );
};