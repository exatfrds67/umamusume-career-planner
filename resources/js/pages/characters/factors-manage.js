/**
 * Factor Management Page Script
 * Handles dynamic form field visibility based on factor type selection
 */

document.addEventListener("DOMContentLoaded", function () {
    const factorTypeSelect = document.getElementById("factor_type");
    const statTypeField = document.getElementById("stat_type_field");
    const aptitudeTypeField = document.getElementById("aptitude_type_field");
    const uniqueSkillField = document.getElementById("unique_skill_field");
    const normalSkillField = document.getElementById("normal_skill_field");

    /**
     * Toggle visibility of conditional fields based on selected factor type
     */
    function toggleFields() {
        const selectedType = factorTypeSelect.value;

        // Hide all conditional fields and remove required attributes
        statTypeField.classList.add("hidden");
        aptitudeTypeField.classList.add("hidden");
        uniqueSkillField.classList.add("hidden");
        normalSkillField.classList.add("hidden");

        document.getElementById("stat_type").required = false;
        document.getElementById("aptitude_type").required = false;
        document.getElementById("unique_skill_name").required = false;
        document.getElementById("normal_skill_name").required = false;

        // Show relevant field based on selection and set required
        switch (selectedType) {
            case "blue_stats":
                statTypeField.classList.remove("hidden");
                document.getElementById("stat_type").required = true;
                break;
            case "red_aptitudes":
                aptitudeTypeField.classList.remove("hidden");
                document.getElementById("aptitude_type").required = true;
                break;
            case "green_unique_skills":
                uniqueSkillField.classList.remove("hidden");
                document.getElementById("unique_skill_name").required = true;
                break;
            case "white_normal_skills":
                normalSkillField.classList.remove("hidden");
                document.getElementById("normal_skill_name").required = true;
                break;
        }
    }

    // Initialize on page load
    if (factorTypeSelect) {
        factorTypeSelect.addEventListener("change", toggleFields);
        toggleFields(); // Run once to set initial state
    }
});
