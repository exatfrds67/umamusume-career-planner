<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Career;
use App\Models\Character;

$targetSkills = ['Xceleration', 'Shifting Gears', 'Iron Will', 'Lay Low', 'Masterful Gambit', 'Front Runner'];

$careers = Career::query()->whereNotNull('career_metadata')->get(['id', 'character_id', 'career_metadata']);

echo 'Searching '.$careers->count()." careers for target skills...\n\n";

foreach ($careers as $career) {
    $meta = $career->career_metadata;
    if (! is_array($meta) && is_string($meta)) {
        $meta = json_decode($meta, true);
    }
    if (! is_array($meta)) {
        continue;
    }
    $skills = isset($meta['skills']) ? $meta['skills'] : [];
    if (empty($skills)) {
        continue;
    }

    foreach ($skills as $skill) {
        $name = isset($skill['name']) ? $skill['name'] : '';
        foreach ($targetSkills as $target) {
            if (stripos($name, $target) !== false) {
                $char = Character::find($career->character_id);
                $charName = ($char && $char->name) ? $char->name : 'Unknown';
                echo "Career #{$career->id} ({$charName}):\n";
                echo "  Skill: {$name}\n";
                $notes = isset($skill['notes']) ? $skill['notes'] : 'NULL';
                $spCost = isset($skill['sp_cost']) ? $skill['sp_cost'] : 'NULL';
                $acquired = isset($skill['acquired']) ? ($skill['acquired'] ? 'true' : 'false') : 'NULL';
                echo "  Notes: {$notes}\n";
                echo "  SP Cost: {$spCost}\n";
                echo "  Acquired: {$acquired}\n\n";
                break;
            }
        }
    }
}

// Sample metadata structure
$sample = $careers->first();
if ($sample) {
    $meta = $sample->career_metadata;
    if (! is_array($meta) && is_string($meta)) {
        $meta = json_decode($meta, true);
    }
    $skills = is_array($meta) && isset($meta['skills']) ? $meta['skills'] : [];
    echo "\n=== Sample metadata skill keys (career #{$sample->id}) ===\n";
    if (! empty($skills)) {
        $first = $skills[0];
        echo 'Keys: '.implode(', ', array_keys($first))."\n";
        echo "First 3 skills:\n";
        foreach (array_slice($skills, 0, 3) as $s) {
            $n = isset($s['notes']) ? $s['notes'] : 'NULL';
            echo '  - '.$s['name'].': notes='.$n."\n";
        }
    }
}
