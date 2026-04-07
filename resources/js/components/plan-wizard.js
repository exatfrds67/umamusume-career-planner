/**
 * planWizard Alpine.js Component
 * 
 * Multi-step wizard for creating/editing career plans.
 * Handles state management, validation, and navigation across 6 steps:
 * 1. Character Selection
 * 2. Goal Setting
 * 3. Skill Selection
 * 4. Race Planning
 * 5. Synergy Analysis
 * 6. Review & Submit
 * 
 * @usage
 * <div x-data="planWizard({ plan: {}, mode: 'create' })" x-init="init()">
 *     <!-- Wizard content -->
 * </div>
 */

export default function planWizard(config = {}) {
    return {
        // Configuration
        mode: config.mode || 'create', // 'create' | 'edit'
        initialPlan: config.plan || null,
        
        // Wizard State
        currentStep: 0,
        steps: [
            { key: 'character', label: 'Character', required: true },
            { key: 'goals', label: 'Goals', required: true },
            { key: 'skills', label: 'Skills', required: false },
            { key: 'races', label: 'Races', required: false },
            { key: 'synergy', label: 'Synergy', required: false },
            { key: 'review', label: 'Review', required: false },
        ],
        completedSteps: [],
        
        // Plan Data
        plan: {
            character_id: null,
            character_name: '',
            star_level: 3,
            goals: {
                target_speed: null,
                target_stamina: null,
                target_power: null,
                target_guts: null,
                target_wit: null,
                target_total_sp: null,
            },
            skills: [],
            races: [],
            notes: '',
        },
        
        // Validation State
        stepErrors: {},
        isSubmitting: false,
        
        // Initialization
        init() {
            // Load initial plan data if editing
            if (this.mode === 'edit' && this.initialPlan) {
                this.plan = { ...this.plan, ...this.initialPlan };
                this.completedSteps = [0, 1, 2, 3, 4, 5]; // Mark all as completed for edit mode
            }
            
            // Listen for character selection
            this.$watch('plan.character_id', (value) => {
                if (value && this.currentStep === 0) {
                    this.validateStep(0);
                }
            });
            
            // Listen for skill selection events
            window.addEventListener('skills-selected', (event) => {
                this.plan.skills = event.detail.skills || [];
                this.validateStep(2);
            });
            
            // Listen for race planning events
            window.addEventListener('races-selected', (event) => {
                this.plan.races = event.detail.races || [];
                this.validateStep(3);
            });
        },
        
        // Navigation Methods
        nextStep() {
            if (this.canProceed) {
                if (!this.completedSteps.includes(this.currentStep)) {
                    this.completedSteps.push(this.currentStep);
                }
                
                if (this.currentStep < this.steps.length - 1) {
                    this.currentStep++;
                    this.scrollToTop();
                }
            }
        },
        
        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.scrollToTop();
            }
        },
        
        goToStep(stepIndex) {
            // Only allow going to previous steps or current step
            if (stepIndex <= this.currentStep || this.completedSteps.includes(stepIndex - 1)) {
                this.currentStep = stepIndex;
                this.scrollToTop();
            }
        },
        
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        
        // Validation Methods
        validateStep(stepIndex) {
            const step = this.steps[stepIndex];
            const errors = [];
            
            switch(step.key) {
                case 'character':
                    if (!this.plan.character_id) {
                        errors.push('Please select a character');
                    }
                    if (this.plan.star_level < 1 || this.plan.star_level > 5) {
                        errors.push('Star level must be between 1 and 5');
                    }
                    break;
                    
                case 'goals':
                    const hasGoal = Object.values(this.plan.goals).some(value => value !== null && value > 0);
                    if (!hasGoal) {
                        errors.push('Please set at least one stat goal');
                    }
                    break;
                    
                case 'skills':
                    // Optional step - no validation errors
                    break;
                    
                case 'races':
                    // Optional step - no validation errors
                    break;
                    
                case 'synergy':
                    // Optional step - informational only
                    break;
                    
                case 'review':
                    // Final validation of all steps
                    if (!this.plan.character_id) {
                        errors.push('Character selection is required');
                    }
                    const hasAnyGoal = Object.values(this.plan.goals).some(value => value !== null && value > 0);
                    if (!hasAnyGoal) {
                        errors.push('At least one stat goal is required');
                    }
                    break;
            }
            
            this.stepErrors[stepIndex] = errors;
            return errors.length === 0;
        },
        
        // Computed Properties
        get isFirstStep() {
            return this.currentStep === 0;
        },
        
        get isLastStep() {
            return this.currentStep === this.steps.length - 1;
        },
        
        get canProceed() {
            return this.validateStep(this.currentStep);
        },
        
        get progressPercentage() {
            return Math.round((this.completedSteps.length / this.steps.length) * 100);
        },
        
        get currentStepErrors() {
            return this.stepErrors[this.currentStep] || [];
        },
        
        // Character Selection Methods
        selectCharacter(character) {
            this.plan.character_id = character.id;
            this.plan.character_name = character.name;
            
            // Auto-advance to next step after selection
            setTimeout(() => {
                if (this.canProceed) {
                    this.nextStep();
                }
            }, 300);
        },
        
        // Star Level Methods
        setStarLevel(level) {
            this.plan.star_level = Math.max(1, Math.min(5, level));
            this.validateStep(0);
        },
        
        get starLevelLabel() {
            return '★'.repeat(this.plan.star_level) + '☆'.repeat(5 - this.plan.star_level);
        },
        
        // Goals Methods
        updateGoal(stat, value) {
            this.plan.goals[stat] = value;
            this.validateStep(1);
        },
        
        // Skills Methods
        addSkill(skill) {
            if (!this.plan.skills.find(s => s.id === skill.id)) {
                this.plan.skills.push(skill);
                this.validateStep(2);
            }
        },
        
        removeSkill(skillId) {
            this.plan.skills = this.plan.skills.filter(s => s.id !== skillId);
            this.validateStep(2);
        },
        
        // Race Methods
        addRace(race) {
            if (!this.plan.races.find(r => r.id === race.id)) {
                this.plan.races.push(race);
                this.validateStep(3);
            }
        },
        
        removeRace(raceId) {
            this.plan.races = this.plan.races.filter(r => r.id !== raceId);
            this.validateStep(3);
        },
        
        // Submission Methods
        async submit() {
            // Validate all steps
            let isValid = true;
            for (let i = 0; i < this.steps.length; i++) {
                if (!this.validateStep(i)) {
                    isValid = false;
                    break;
                }
            }
            
            if (!isValid) {
                this.$dispatch('wizard-error', { 
                    message: 'Please complete all required steps before submitting' 
                });
                return;
            }
            
            this.isSubmitting = true;
            
            try {
                const endpoint = this.mode === 'edit' 
                    ? `/api/plans/${this.initialPlan.id}` 
                    : '/api/plans';
                    
                const method = this.mode === 'edit' ? 'PUT' : 'POST';
                
                const response = await fetch(endpoint, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.plan),
                });
                
                if (!response.ok) {
                    throw new Error('Failed to save plan');
                }
                
                const data = await response.json();
                
                this.$dispatch('wizard-success', { 
                    plan: data.plan,
                    message: this.mode === 'edit' ? 'Plan updated successfully' : 'Plan created successfully'
                });
                
                // Redirect to plan detail page
                window.location.href = `/plans/${data.plan.id}`;
                
            } catch (error) {
                console.error('Plan submission error:', error);
                this.$dispatch('wizard-error', { 
                    message: error.message || 'Failed to save plan. Please try again.'
                });
            } finally {
                this.isSubmitting = false;
            }
        },
        
        // Draft Management
        saveDraft() {
            const draftKey = this.mode === 'edit' 
                ? `plan-draft-${this.initialPlan.id}` 
                : 'plan-draft-new';
                
            localStorage.setItem(draftKey, JSON.stringify({
                plan: this.plan,
                currentStep: this.currentStep,
                completedSteps: this.completedSteps,
                savedAt: new Date().toISOString(),
            }));
            
            this.$dispatch('draft-saved', { message: 'Draft saved' });
        },
        
        loadDraft() {
            const draftKey = this.mode === 'edit' 
                ? `plan-draft-${this.initialPlan.id}` 
                : 'plan-draft-new';
                
            const draftData = localStorage.getItem(draftKey);
            
            if (draftData) {
                try {
                    const draft = JSON.parse(draftData);
                    this.plan = draft.plan;
                    this.currentStep = draft.currentStep || 0;
                    this.completedSteps = draft.completedSteps || [];
                    
                    this.$dispatch('draft-loaded', { 
                        message: 'Draft loaded',
                        savedAt: draft.savedAt 
                    });
                } catch (error) {
                    console.error('Failed to load draft:', error);
                }
            }
        },
        
        clearDraft() {
            const draftKey = this.mode === 'edit' 
                ? `plan-draft-${this.initialPlan.id}` 
                : 'plan-draft-new';
                
            localStorage.removeItem(draftKey);
            this.$dispatch('draft-cleared');
        },
    };
}
