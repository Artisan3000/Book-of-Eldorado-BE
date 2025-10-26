<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;

$slug = $argv[1] ?? 'foundation';
$c = Course::with(['discussions.user'])->where('slug', $slug)->first();
if (! $c) {
    echo "Course with slug '$slug' not found\n";
    exit(0);
}

echo "Found course id: {$c->id}\n";
echo "Discussions: " . $c->discussions->count() . "\n";
foreach ($c->discussions as $d) {
    $author = $d->user?->name ?? 'null';
    echo "Discussion {$d->id} author: {$author}\n";
}

echo "Done\n";
