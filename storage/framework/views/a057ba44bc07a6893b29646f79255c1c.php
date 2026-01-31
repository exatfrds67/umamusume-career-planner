<div class="flex flex-col items-center gap-4">
    <!-- SVG Radar Chart -->
    <div class="relative <?php echo e($getSizeClasses()); ?>" role="img" aria-label="Stat radar chart">
        <svg viewBox="0 0 <?php echo e($size === 'sm' ? 64 : ($size === 'lg' ? 192 : 128)); ?> <?php echo e($size === 'sm' ? 64 : ($size === 'lg' ? 192 : 128)); ?>"
            class="w-full h-full" xmlns="http://www.w3.org/2000/svg">

            <!-- Background grid -->
            <defs>
                <style>
                    .grid-line {
                        stroke: currentColor;
                        stroke-width: 0.5;
                        opacity: 0.2;
                        fill: none;
                    }

                    .grid-label {
                        fill: currentColor;
                        opacity: 0.5;
                        font-size: <?php echo e($size === 'sm' ? '3px' : ($size === 'lg' ? '9px' : '6px')); ?>;
                        font-weight: 500;
                        text-anchor: middle;
                    }

                    .radar-fill {
                        opacity: 0.3;

                        <?php if($animated): ?>
                            animation: radarFill 1.2s ease-out forwards;
                        <?php endif; ?>
                    }

                    <?php if($animated): ?>
                        @keyframes radarFill {
                            from {
                                opacity: 0;
                            }

                            to {
                                opacity: 0.3;
                            }
                        }
                    <?php endif; ?>
                </style>
            </defs>

            <!-- Grid circles/pentagons -->
            <?php $__currentLoopData = $getGridPoints(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level => $gridPointsString): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <polygon points="<?php echo e($gridPointsString); ?>" class="grid-line dark:stroke-gray-600" />
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <!-- Grid value labels -->
            <?php
                $svgSize = $size === 'sm' ? 64 : ($size === 'lg' ? 192 : 128);
                $centerX = $svgSize / 2;
                $centerY = $svgSize / 2;
                $maxRadius = $svgSize / 2.2;
                $labelOffset = $size === 'sm' ? 2 : ($size === 'lg' ? 6 : 4);
            ?>
            <?php $__currentLoopData = [1, 2, 3, 4, 5]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $radius = ($level / 5) * $maxRadius;
                    $value = round(($level / 5) * $max);
                    // Position label at top (12 o'clock position)
                    $labelY = $centerY - $radius - $labelOffset;
                ?>
                <text x="<?php echo e($centerX); ?>" y="<?php echo e($labelY); ?>"
                    class="grid-label dark:fill-gray-400"><?php echo e($value); ?></text>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <!-- Grid radial lines from center -->
            <?php
                $svgSize = $size === 'sm' ? 64 : ($size === 'lg' ? 192 : 128);
                $centerX = $svgSize / 2;
                $centerY = $svgSize / 2;
                $maxRadius = $svgSize / 2.2;
            ?>
            <?php $__currentLoopData = $getStatNames(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $angle = ($index * 360) / 5 - 90;
                    $radians = deg2rad($angle);
                    $x = $centerX + $maxRadius * cos($radians);
                    $y = $centerY + $maxRadius * sin($radians);
                ?>
                <line x1="<?php echo e($centerX); ?>" y1="<?php echo e($centerY); ?>" x2="<?php echo e($x); ?>"
                    y2="<?php echo e($y); ?>" class="grid-line dark:stroke-gray-600" />
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <!-- Data polygon -->
            <polygon points="<?php echo e(implode(' ', $calculatePoints())); ?>"
                class="radar-fill <?php echo e(match ($stats['speed'] ?? 0) {default => 'fill-blue-400/30'}); ?> dark:fill-blue-500/20"
                style="fill: url(#radarGradient);" />

            <!-- Gradient for radar fill -->
            <defs>
                <linearGradient id="radarGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color: #fb7185; stop-opacity: 0.3" />
                    <stop offset="100%" style="stop-color: #0ea5e9; stop-opacity: 0.3" />
                </linearGradient>
            </defs>

            <!-- Data points -->
            <?php $__currentLoopData = $getStatNames(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $percentage = $getStatPercentages()[$stat];
                    $angle = ($index * 360) / 5 - 90;
                    $radians = deg2rad($angle);
                    $radius = ($percentage / 100) * $maxRadius;
                    $x = $centerX + $radius * cos($radians);
                    $y = $centerY + $radius * sin($radians);
                ?>
                <circle cx="<?php echo e($x); ?>" cy="<?php echo e($y); ?>" r="3" class="<?php echo e($getStatColor($stat)); ?>"
                    style="fill: <?php echo e($getSvgFillColor($stat)); ?>;" role="presentation">
                    <title><?php echo e(ucfirst($stat)); ?>: <?php echo e($getStatValue($stat)); ?></title>
                </circle>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </svg>
    </div>

    <!-- Legend -->
    <?php if($showLabels): ?>
        <div class="flex items-center justify-center gap-x-2 gap-y-1 flex-wrap max-w-full">
            <?php $__currentLoopData = $getStatNames(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full flex-shrink-0"
                        style="background-color: <?php echo e($getSvgFillColor($stat)); ?>;"></span>
                    <span class="text-xs font-medium whitespace-nowrap <?php echo e($getStatColor($stat)); ?>">
                        <?php echo e(ucfirst($stat)); ?>

                        <?php if($showValues): ?>
                            <span class="text-[10px] opacity-75">(<?php echo e($getStatValue($stat)); ?>)</span>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/stat-radar-chart.blade.php ENDPATH**/ ?>