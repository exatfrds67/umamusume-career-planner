
<?php
    $statColors = [
        'speed' => 'text-blue-600 dark:text-blue-400',
        'stamina' => 'text-emerald-600 dark:text-emerald-400',
        'power' => 'text-red-600 dark:text-red-400',
        'guts' => 'text-orange-600 dark:text-orange-400',
        'wit' => 'text-purple-600 dark:text-purple-400',
    ];
    $getGrade = function ($value) {
        if ($value >= 1200) {
            return ['SS', 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'];
        }
        if ($value >= 1100) {
            return ['S', 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300'];
        }
        if ($value >= 901) {
            return ['A', 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'];
        }
        if ($value >= 701) {
            return ['B', 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'];
        }
        if ($value >= 501) {
            return ['C', 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'];
        }
        if ($value >= 301) {
            return ['D', 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'];
        }
        return ['E', 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'];
    };
    $getMultiplier = function ($level) {
        return match ((int) $level) {
            1 => '1.00×',
            2 => '1.25×',
            3 => '1.50×',
            4 => '1.75×',
            5 => '2.00×',
            default => '1.00×',
        };
    };
?>

<section class="card rounded-xl p-6 mb-6 animate-fade-in-delay-2" aria-labelledby="char-overview-heading">
    <h2 id="char-overview-heading" class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
        <?php echo e($character->name); ?>

    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        
        <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center justify-between">
                Current Stats
                <span class="text-xs text-gray-500">Soft Cap: 1200</span>
            </h3>
            <div class="space-y-2">
                <?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $statValue = $character->current_stats[$stat] ?? 0;
                        $grade = $getGrade($statValue);
                        $isOverCap = $statValue > 1200;
                    ?>
                    <div class="flex justify-between items-center">
                        <span class="text-sm <?php echo e($statColors[$stat]); ?> capitalize font-medium"><?php echo e($stat); ?></span>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-sm font-semibold text-gray-900 dark:text-white <?php echo e($isOverCap ? 'text-yellow-600 dark:text-yellow-400' : ''); ?>">
                                <?php echo e($statValue); ?>

                                <?php if($isOverCap): ?>
                                    <span class="text-xs text-yellow-600"
                                        title="Above soft cap - gains reduced to +50 max">⚠️</span>
                                <?php endif; ?>
                            </span>
                            <span
                                class="px-1.5 py-0.5 rounded text-xs font-medium <?php echo e($grade[1]); ?>"><?php echo e($grade[0]); ?></span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                            <?php echo e(array_sum($character->current_stats ?? [])); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if($character->scenario_type === 'unity_cup'): ?>
            <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center justify-between">
                    Facility Levels
                    <span class="text-xs text-gray-500">1.0×-2.0×</span>
                </h3>
                <div class="space-y-2">
                    <?php $facilityLevels = $character->facility_levels ?? []; ?>
                    <?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $level = $facilityLevels[$stat] ?? 1;
                            $multiplier = $getMultiplier($level);
                        ?>
                        <div class="flex justify-between items-center">
                            <span
                                class="text-sm <?php echo e($statColors[$stat]); ?> capitalize font-medium"><?php echo e($stat); ?></span>
                            <div class="flex items-center gap-2">
                                <div class="flex gap-0.5">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <div
                                            class="w-2 h-2 rounded-full <?php echo e($i <= $level ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600'); ?>">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <span
                                    class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($multiplier); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Growth Rates</h3>
                <div class="space-y-2">
                    <?php $growthRates = $character->growth_rates ?? []; ?>
                    <?php $__currentLoopData = ['speed', 'stamina', 'power', 'guts', 'wit']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $rate = $growthRates[$stat] ?? 0; ?>
                        <div class="flex justify-between items-center">
                            <span
                                class="text-sm <?php echo e($statColors[$stat]); ?> capitalize font-medium"><?php echo e($stat); ?></span>
                            <span
                                class="text-sm font-semibold <?php echo e($rate > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white'); ?>">
                                +<?php echo e($rate); ?>%
                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Support Cards</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        <?php echo e($character->supportCards->count()); ?>/6 cards equipped
                    </span>
                </div>
                <?php
                    $rainbowCount = $character->supportCards->filter(fn($c) => ($c->bond_level ?? 0) >= 80)->count();
                    $cardsByType = $character->supportCards->groupBy(fn($c) => $c->supportCard->card_type ?? 'unknown');
                ?>
                <?php if($rainbowCount > 0): ?>
                    <div class="flex items-center gap-2">
                        <span class="text-yellow-500">💛</span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            <?php echo e($rainbowCount); ?> at friendship (80%+)
                        </span>
                    </div>
                <?php endif; ?>
                
                <div class="flex flex-wrap gap-1 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <?php $__currentLoopData = $cardsByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $cards): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span
                            class="px-2 py-0.5 rounded text-xs font-medium <?php echo e($statColors[$type] ?? 'text-gray-600'); ?> bg-gray-100 dark:bg-gray-700">
                            <?php echo e(ucfirst($type)); ?>: <?php echo e($cards->count()); ?>

                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/training/partials/character-overview.blade.php ENDPATH**/ ?>