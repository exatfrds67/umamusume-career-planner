
<div class="space-y-6">
    <!-- Race Basic Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="race_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Race Name
            </label>
            <input type="text" id="race_name" name="race_name" value="<?php echo e($data['race_name'] ?? ''); ?>"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="e.g., Japan Cup" aria-label="Race name">
        </div>

        <div>
            <label for="race_grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Race Grade
            </label>
            <select id="race_grade" name="race_grade"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                aria-label="Race grade">
                <option value="">-- Select Grade --</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['G1', 'G2', 'G3', 'OP', 'Pre-OP']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($grade); ?>" <?php echo e(($data['race_grade'] ?? '') === $grade ? 'selected' : ''); ?>>
                        <?php echo e($grade); ?>

                    </option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Race Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="distance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Distance (m)
            </label>
            <input type="number" id="distance" name="distance" value="<?php echo e($data['distance'] ?? ''); ?>" min="1000"
                max="3600"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="1000-3600" aria-label="Race distance in meters">
        </div>

        <div>
            <label for="surface" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Surface
            </label>
            <select id="surface" name="surface"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                aria-label="Race surface">
                <option value="">-- Select Surface --</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['turf' => 'Turf', 'dirt' => 'Dirt']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($value); ?>" <?php echo e(($data['surface'] ?? '') === $value ? 'selected' : ''); ?>>
                        <?php echo e($label); ?>

                    </option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>

        <div>
            <label for="distance_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Distance Category
            </label>
            <select id="distance_category" name="distance_category"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                aria-label="Distance category">
                <option value="">-- Select Category --</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['sprint' => 'Sprint', 'mile' => 'Mile', 'medium' => 'Medium', 'long' => 'Long']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($value); ?>"
                        <?php echo e(($data['distance_category'] ?? '') === $value ? 'selected' : ''); ?>>
                        <?php echo e($label); ?>

                    </option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Race Result -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Final Position
            </label>
            <input type="number" id="position" name="position" value="<?php echo e($data['position'] ?? ''); ?>" min="1"
                max="18"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="1-18" aria-label="Final race position">
        </div>

        <div>
            <label for="outcome" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Outcome
            </label>
            <select id="outcome" name="outcome"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                aria-label="Race outcome">
                <option value="">-- Select Outcome --</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['victory' => 'Victory (1st)', 'podium' => 'Podium (2nd-3rd)', 'top_5' => 'Top 5', 'defeat' => 'Defeat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($value); ?>" <?php echo e(($data['outcome'] ?? '') === $value ? 'selected' : ''); ?>>
                        <?php echo e($label); ?>

                    </option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Rewards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="fans_gained" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Fans Gained
            </label>
            <input type="number" id="fans_gained" name="fans_gained" value="<?php echo e($data['fans_gained'] ?? ''); ?>"
                min="0" max="999999"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="0" aria-label="Fans gained from race">
        </div>

        <div>
            <label for="skill_points_gained" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Skill Points Gained
            </label>
            <input type="number" id="skill_points_gained" name="skill_points_gained"
                value="<?php echo e($data['skill_points_gained'] ?? ''); ?>" min="0" max="9999"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="0" aria-label="Skill points gained from race">
        </div>
    </div>

    <!-- Race Date -->
    <div>
        <label for="race_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Race Date (Optional)
        </label>
        <input type="date" id="race_date" name="race_date" value="<?php echo e($data['race_date'] ?? ''); ?>"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            aria-label="Race date">
    </div>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($data['errors']) && !empty($data['errors'])): ?>
    <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">⚠️ Validation Warnings</h4>
        <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $data['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <li><?php echo e($error); ?></li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ul>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/ocr/partials/race-result-form.blade.php ENDPATH**/ ?>