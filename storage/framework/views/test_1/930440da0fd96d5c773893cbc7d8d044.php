
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Stats Section -->
    <div class="space-y-4">
        <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Character Stats</h3>

        <?php $__currentLoopData = ['speed' => 'Speed', 'stamina' => 'Stamina', 'power' => 'Power', 'guts' => 'Guts', 'wit' => 'Wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
                <label for="stat_<?php echo e($stat); ?>"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <?php echo e($label); ?>

                    <?php if(isset($data['stats'][$stat]) && $data['stats'][$stat] !== null): ?>
                        <span class="text-xs text-gray-500">(Confidence:
                            <?php echo e(number_format(($data['confidence'] ?? 0.5) * 100, 0)); ?>%)</span>
                    <?php endif; ?>
                </label>
                <input type="number" id="stat_<?php echo e($stat); ?>" name="stats[<?php echo e($stat); ?>]"
                    value="<?php echo e($data['stats'][$stat] ?? ''); ?>" min="0" max="1200"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 <?php echo e(isset($data['stats'][$stat]) && $data['stats'][$stat] === null ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-300'); ?>"
                    placeholder="0-1200" aria-label="<?php echo e($label); ?> stat value">
                <?php if(isset($data['stats'][$stat]) && $data['stats'][$stat] === null): ?>
                    <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">⚠️ Not extracted - please enter
                        manually</p>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Additional Info Section -->
    <div class="space-y-4">
        <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Additional Information</h3>

        <!-- Energy Level -->
        <div>
            <label for="energy_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Energy Level (%)
            </label>
            <input type="number" id="energy_level" name="energy_level" value="<?php echo e($data['energy_level'] ?? ''); ?>"
                min="0" max="100"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="0-100" aria-label="Energy level percentage">
        </div>

        <!-- Mood Status -->
        <div>
            <label for="mood_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Mood Status
            </label>
            <select id="mood_status" name="mood_status"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                aria-label="Character mood status">
                <option value="">-- Select Mood --</option>
                <?php $__currentLoopData = ['great' => 'Great', 'good' => 'Good', 'normal' => 'Normal', 'bad' => 'Bad', 'awful' => 'Awful']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>"
                        <?php echo e(($data['mood_status'] ?? '') === $value ? 'selected' : ''); ?>>
                        <?php echo e($label); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <!-- Current Turn -->
        <div>
            <label for="current_turn" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Current Turn
            </label>
            <input type="number" id="current_turn" name="current_turn" value="<?php echo e($data['current_turn'] ?? ''); ?>"
                min="1" max="78"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="1-78" aria-label="Current turn number">
        </div>

        <!-- Total Turns -->
        <div>
            <label for="total_turns" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Total Turns
            </label>
            <input type="number" id="total_turns" name="total_turns" value="<?php echo e($data['total_turns'] ?? 78); ?>"
                min="1" max="78"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="78" aria-label="Total turns in career">
        </div>
    </div>
</div>

<?php if(isset($data['errors']) && !empty($data['errors'])): ?>
    <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">⚠️ Validation Warnings</h4>
        <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
            <?php $__currentLoopData = $data['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/ocr/partials/character-stats-form.blade.php ENDPATH**/ ?>