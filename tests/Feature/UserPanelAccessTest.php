<?php

use App\Models\User;
use Filament\Panel;

it('allows pharmacist users to access the pharmacist panel', function () {
    $user = new User([
        'role' => 'Pharmacist',
    ]);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->once()->andReturn('pharmacist');

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('denies cashier users from pharmacist panel access', function () {
    $user = new User([
        'role' => 'cashier',
    ]);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->once()->andReturn('pharmacist');

    expect($user->canAccessPanel($panel))->toBeFalse();
});

it('returns empty tenants outside cashier and pharmacist panels', function () {
    $user = new User([
        'branch_id' => 1,
        'role' => 'Pharmacist',
    ]);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->once()->andReturn('admin');

    $tenants = $user->getTenants($panel);

    expect($tenants)->toBeEmpty();
});

it('returns empty tenants when pharmacist has no branch assignment', function () {
    $user = new User([
        'role' => 'Pharmacist',
    ]);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->once()->andReturn('pharmacist');

    $tenants = $user->getTenants($panel);

    expect($tenants)->toBeEmpty();
});
