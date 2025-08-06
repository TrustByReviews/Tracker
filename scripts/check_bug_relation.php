<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$bugId = 'efce1678-7392-42ac-a721-f7b44db680bd';
$bug = Bug::find($bugId);

echo "Bug ID: " . $bug->id . "\n";
echo "Bug Title: " . $bug->title . "\n";
echo "Related Task ID: " . ($bug->related_task_id ?? 'NULL') . "\n";

if ($bug->related_task_id) {
    $relatedTask = \App\Models\Task::find($bug->related_task_id);
    if ($relatedTask) {
        echo "Related Task Name: " . $relatedTask->name . "\n";
    } else {
        echo "Related Task not found\n";
    }
} else {
    echo "No related task set\n";
} 