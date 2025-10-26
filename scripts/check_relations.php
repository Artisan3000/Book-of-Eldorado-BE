<?php

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the framework
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;

$slug = $argv[1] ?? 'foundation';
$c = Course::with(['lessons','quizzes','projects','discussions'])->where('slug', $slug)->first();
if (! $c) {
    echo "Course with slug '$slug' not found\n";
    exit(0);
}

echo "Found course id: {$c->id}\n";
echo "Lessons: " . $c->lessons->count() . "\n";
echo "Quizzes: " . $c->quizzes->count() . "\n";
echo "Projects: " . $c->projects->count() . "\n";
echo "Discussions: " . $c->discussions->count() . "\n";

// Optionally list ids
if ($c->lessons->count()) {
    echo "Lesson IDs: " . implode(',', $c->lessons->pluck('id')->toArray()) . "\n";
}
if ($c->quizzes->count()) {
    echo "Quiz IDs: " . implode(',', $c->quizzes->pluck('id')->toArray()) . "\n";
}
if ($c->projects->count()) {
    echo "Project IDs: " . implode(',', $c->projects->pluck('id')->toArray()) . "\n";
}
if ($c->discussions->count()) {
    echo "Discussion IDs: " . implode(',', $c->discussions->pluck('id')->toArray()) . "\n";
}

echo "Done\n";
