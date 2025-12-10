<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// TEMPORARY: Seed route for demo setup - REMOVE AFTER USE
Route::get('/setup-demo-seed-now', function () {
    // Run migrations first
    Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = Artisan::output();

    // Run seeder
    Artisan::call('db:seed', ['--force' => true]);
    $seedOutput = Artisan::output();

    return response()->json([
        'status' => 'success',
        'migrate_output' => $migrateOutput,
        'seed_output' => $seedOutput,
    ]);
});
