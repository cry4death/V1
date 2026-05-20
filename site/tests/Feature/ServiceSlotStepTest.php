<?php

use App\Models\Service;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('service slot step uses booking setting when set', function (): void {
    Setting::query()->create([
        'group_name' => 'booking',
        'key' => 'slot_step_minutes',
        'value' => '20',
    ]);

    $service = new Service(['duration_minutes' => 45]);

    expect($service->slotStepMinutes())->toBe(20);
});

test('service slot step falls back to duration when booking setting absent', function (): void {
    $service = new Service(['duration_minutes' => 40]);

    expect($service->slotStepMinutes())->toBe(40);
});
