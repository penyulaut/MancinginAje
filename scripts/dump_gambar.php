<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$products = App\Models\Products::limit(5)->get();
if ($products->isEmpty()) {
    echo "No products found\n";
} else {
    foreach ($products as $p) {
        echo $p->id . " => " . ($p->gambar ?? '(null)') . PHP_EOL;
    }
}
