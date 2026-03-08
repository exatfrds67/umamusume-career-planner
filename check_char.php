<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$char = \App\Models\Character::find(210);
if ($char) {
    echo 'Character: '.$char->name.PHP_EOL;
    echo 'Character current_turn (attribute): '.$char->current_turn.PHP_EOL;
    echo 'Current stats JSON: '.json_encode($char->current_stats).PHP_EOL;

    $career = $char->currentCareer;
    if ($career) {
        echo 'Career exists: ID '.$career->id.PHP_EOL;
        echo 'Career current_turn: '.$career->current_turn.PHP_EOL;

        $sessions = $career->trainingSessions()->get();
        echo 'Training sessions count: '.$sessions->count().PHP_EOL;
        if ($sessions->count() > 0) {
            echo 'Latest training: ';
            $latest = $sessions->first();
            echo $latest->training_type.' on turn '.$latest->turn_number.PHP_EOL;
            echo '  Gains: speed='.$latest->speed_gain.', power='.$latest->power_gain.PHP_EOL;
        }
    } else {
        echo 'No current career'.PHP_EOL;
    }
} else {
    echo 'Character not found'.PHP_EOL;
}
