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
    const characterId = document.getElementById("training-predictions-app")?.getAttribute("data-character-id");
    if (!characterId) {
        console.error("Character ID not found");
        return;
    }

    // Create a form and submit it to the training store endpoint
    const form = document.createElement("form");
    form.method = "POST";
    form.action = `/characters/${characterId}/training`;
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    if (csrfToken) {
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "_token";
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }
    
    // Add training type
    const trainingInput = document.createElement("input");
    trainingInput.type = "hidden";
    trainingInput.name = "training_type";
    trainingInput.value = facility;
    form.appendChild(trainingInput);
    
    // Submit the form
    document.body.appendChild(form);
    form.submit();};