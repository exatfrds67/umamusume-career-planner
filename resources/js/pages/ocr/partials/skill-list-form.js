/**
 * OCR Skill List Form
 * Handles dynamic skill addition/removal in OCR results
 */

document.addEventListener("DOMContentLoaded", () => {
    // Get initial skill count from JSON data island
    const dataEl = document.getElementById("skill-list-form-data");
    const pageData = dataEl ? JSON.parse(dataEl.textContent) : {};
    let skillIndex = pageData.initialSkillCount ?? 0;

    // Add skill button
    const addButton = document.getElementById("add-skill-button");
    if (addButton) {
        addButton.addEventListener("click", function () {
            const container = document.getElementById("skills-container");
            if (container) {
                const newSkill = createSkillItem(skillIndex++);
                container.insertAdjacentHTML("beforeend", newSkill);
                attachRemoveHandlers();
            }
        });
    }

    // Remove skill handlers
    function attachRemoveHandlers() {
        document.querySelectorAll(".remove-skill-button").forEach((button) => {
            button.removeEventListener("click", handleRemove); // Prevent duplicate listeners
            button.addEventListener("click", handleRemove);
        });
    }

    function handleRemove() {
        this.closest(".skill-item")?.remove();
    }

    function createSkillItem(index) {
        return `
            <div class="skill-item bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-5">
                        <label for="skill_name_${index}" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Skill Name</label>
                        <input type="text" id="skill_name_${index}" name="skills[${index}][name]" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white" placeholder="Skill name">
                    </div>
                    <div class="md:col-span-2">
                        <label for="skill_sp_${index}" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SP Cost</label>
                        <input type="number" id="skill_sp_${index}" name="skills[${index}][sp_cost]" min="0" max="500" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white" placeholder="0">
                    </div>
                    <div class="md:col-span-2">
                        <label for="skill_hint_${index}" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Hint Level</label>
                        <input type="number" id="skill_hint_${index}" name="skills[${index}][hint_level]" min="0" max="5" value="0" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white" placeholder="0">
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <label class="flex items-center">
                            <input type="checkbox" name="skills[${index}][is_acquired]" value="1" class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">Acquired</span>
                        </label>
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button type="button" class="remove-skill-button w-full px-2 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg focus:outline-none transition-colors">
                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // Initial attachment
    attachRemoveHandlers();
});
