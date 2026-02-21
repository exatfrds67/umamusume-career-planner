

<?php $__env->startSection('content'); ?>
    <?php
        /** @var \App\Models\Character $character */
    ?>
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1
                    class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Edit <?php echo e($character->name); ?>

                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Update character stats, goals, and tracking information
                </p>
            </div>
            <a href="<?php echo e(route('characters.show', $character)); ?>" class="btn btn-secondary">
                Cancel
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span><?php echo e(session('success')); ?></span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert alert-error">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <li><?php echo e($error); ?></li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('characters.update', $character)); ?>" class="space-y-6"
            id="character-form">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <!-- Row 1: Basic Info (Left) + Stats (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information & Status -->
                <div class="glass-card rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Basic Information &
                            Status</h3>
                    </div>
                    <div class="card-body grid grid-cols-1 gap-4">
                        <div>
                            <label for="name" class="form-label">Character Name</label>
                            <input type="text" id="name" name="name"
                                value="<?php echo e(old('name', $character->name)); ?>" required maxlength="100" class="form-input">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="career_stage" class="form-label">Career Stage</label>
                                <select id="career_stage" name="career_stage" class="form-select">
                                    <option value="junior"
                                        <?php echo e(old('career_stage', $character->career_stage) === 'junior' ? 'selected' : ''); ?>>
                                        Junior</option>
                                    <option value="classic"
                                        <?php echo e(old('career_stage', $character->career_stage) === 'classic' ? 'selected' : ''); ?>>
                                        Classic</option>
                                    <option value="senior"
                                        <?php echo e(old('career_stage', $character->career_stage) === 'senior' ? 'selected' : ''); ?>>
                                        Senior</option>
                                </select>
                            </div>

                            <div>
                                <label for="current_turn" class="form-label">Turn (1-78)</label>
                                <input type="number" id="current_turn" name="current_turn"
                                    value="<?php echo e(old('current_turn', $character->current_turn)); ?>" min="1"
                                    max="78" class="form-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="energy_level" class="form-label">Energy (%)</label>
                                <input type="number" id="energy_level" name="energy_level"
                                    value="<?php echo e(old('energy_level', $character->energy_level)); ?>" min="0"
                                    max="100" class="form-input">
                            </div>

                            <div>
                                <label for="mood_status" class="form-label">Mood</label>
                                <select id="mood_status" name="mood_status" class="form-select">
                                    <option value="awful"
                                        <?php echo e(old('mood_status', $character->mood_status) === 'awful' ? 'selected' : ''); ?>>
                                        Awful (-20%)</option>
                                    <option value="bad"
                                        <?php echo e(old('mood_status', $character->mood_status) === 'bad' ? 'selected' : ''); ?>>
                                        Bad (-10%)</option>
                                    <option value="normal"
                                        <?php echo e(old('mood_status', $character->mood_status) === 'normal' ? 'selected' : ''); ?>>
                                        Normal</option>
                                    <option value="good"
                                        <?php echo e(old('mood_status', $character->mood_status) === 'good' ? 'selected' : ''); ?>>
                                        Good (+10%)</option>
                                    <option value="great"
                                        <?php echo e(old('mood_status', $character->mood_status) === 'great' ? 'selected' : ''); ?>>
                                        Great (+20%)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Stats -->
                <div class="glass-card rounded-lg">
                    <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Current Stats</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Values between 0-1200</p>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div>
                                    <label for="stat_<?php echo e($stat); ?>" class="form-label capitalize text-xs">
                                        <?php echo e($stat); ?>

                                        <span class="text-xs text-gray-500 ml-1 font-bold">
                                            <?php echo e($character->getStatGrade($character->current_stats[$stat] ?? 0)); ?>

                                        </span>
                                    </label>
                                    <input type="number" id="stat_<?php echo e($stat); ?>"
                                        name="stats[<?php echo e($stat); ?>]"
                                        value="<?php echo e(old('stats.' . $stat, $character->current_stats[$stat] ?? 0)); ?>"
                                        min="0" max="1200" step="1" class="form-input stat-input"
                                        data-stat="<?php echo e($stat); ?>" oninput="enforceStatMax(this)">
                                    <p id="stat_<?php echo e($stat); ?>_error" class="text-xs text-red-500 hidden mt-1">Max
                                        1200</p>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Goals (Full Width) -->
            <div class="glass-card-alt rounded-lg">
                <div class="card-header bg-transparent border-b border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Training Goals</h3>
                </div>
                <div class="card-body space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <div>
                                <label for="goal_<?php echo e($stat); ?>" class="form-label capitalize text-xs">Target
                                    <?php echo e($stat); ?></label>
                                <input type="number" id="goal_<?php echo e($stat); ?>"
                                    name="goals[target_stats][<?php echo e($stat); ?>]"
                                    value="<?php echo e(old('goals.target_stats.' . $stat, $character->goals['target_stats'][$stat] ?? '')); ?>"
                                    min="0" max="1200" step="1" placeholder="Optional"
                                    class="form-input stat-input" oninput="enforceStatMax(this)">
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    <div>
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="goals[notes]" rows="4" class="form-input"
                            placeholder="Training strategy notes..."><?php echo e(old('goals.notes', $character->goals['notes'] ?? '')); ?></textarea>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-t border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between">
                    <button type="button"
                        onclick="if(confirm('Delete character? This cannot be undone.')) document.getElementById('delete-form').submit()"
                        class="text-red-600 hover:text-red-800 text-sm font-medium focus:outline-none">
                        Delete Character
                    </button>
                    <div class="flex gap-4">
                        <a href="<?php echo e(route('characters.show', $character)); ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <form id="delete-form" method="POST" action="<?php echo e(route('characters.destroy', $character)); ?>"
            class="hidden">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
        </form>
    </div>

    <script>
        function enforceStatMax(input) {
            const max = 1200;
            const errorId = input.id.includes('goal_') ? null : input.id + '_error';
            const errorEl = errorId ? document.getElementById(errorId) : null;

            if (parseInt(input.value) > max) {
                input.value = max;
                if (errorEl) {
                    errorEl.classList.remove('hidden');
                    setTimeout(() => errorEl.classList.add('hidden'), 2000);
                }
            }

            if (parseInt(input.value) < 0) {
                input.value = 0;
            }
        }

        document.getElementById('character-form').addEventListener('submit', function(e) {
            const statInputs = document.querySelectorAll('.stat-input');
            let hasError = false;

            statInputs.forEach(input => {
                if (parseInt(input.value) > 1200) {
                    input.value = 1200;
                    hasError = true;
                }
            });

            if (hasError) {
                // Just notify, form still submits with corrected values
                // alert('Stats capped to 1200');
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/characters/edit.blade.php ENDPATH**/ ?>