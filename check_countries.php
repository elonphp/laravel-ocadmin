<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$countries = \App\Models\Geo\Country::whereNull('native_name')
    ->orWhere('native_name', '')
    ->get(['id', 'name', 'iso_code_2']);

echo "Missing native_name count: " . $countries->count() . "\n\n";
foreach ($countries as $c) {
    echo "{$c->iso_code_2}\t{$c->name}\n";
}
