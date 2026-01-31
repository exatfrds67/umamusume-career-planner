<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', config('app.name', 'Umamusume Career Planner')); ?></title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="icon" type="image/x-icon" sizes="16x16"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_16.ico')); ?>">
    <link rel="icon" type="image/x-icon" sizes="32x32"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_32.ico')); ?>">
    <link rel="icon" type="image/png" sizes="128x128"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_128.png')); ?>">
    <link rel="icon" type="image/png" sizes="256x256"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_256.png')); ?>">
    <link rel="icon" type="image/png" sizes="512x512"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_512.png')); ?>">
    <link rel="apple-touch-icon" sizes="128x128"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_128.png')); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" media="print"
        onload="this.media='all'" />
    <noscript>
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    </noscript>

    <!-- Synchronous Theme Initialization (prevents flash/mismatch on page load) -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Scripts & Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900">
    <!-- Fixed Background with Theme-Aware Images -->
    <div id="app-background" class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500"
        data-bg-light-desktop="/images/app_bg/uma_musume_race_planner_bg_light_1536x1028.png"
        data-bg-light-mobile="/images/app_bg/uma_musume_race_planner_bg_light_1028x1536.png"
        data-bg-dark-desktop="/images/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png"
        data-bg-dark-mobile="/images/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png" aria-hidden="true">
    </div>

    <!-- Content -->
    <div class="relative z-10">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/layouts/guest.blade.php ENDPATH**/ ?>