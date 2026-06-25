<?php

use App\Actions\User\CreateUserAction;
use App\Actions\User\DeactivateUserAction;
use App\Models\User;

it('can create user with salesman role', function () {
    $action = app(CreateUserAction::class);

    $user = $action->execute([
        'name' => 'Budi Santoso',
        'email' => 'budi@falaya.id',
        'password' => 'password',
        'role' => 'SALESMAN',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->role)->toBe('SALESMAN')
        ->and($user->is_active)->toBeTrue()
        ->and($user->hasRole('SALESMAN'))->toBeTrue();
});

it('can create user with admin role', function () {
    $action = app(CreateUserAction::class);

    $user = $action->execute([
        'name' => 'Siti Admin',
        'email' => 'siti@falaya.id',
        'password' => 'password',
        'role' => 'ADMIN',
    ]);

    expect($user->role)->toBe('ADMIN')
        ->and($user->hasRole('ADMIN'))->toBeTrue()
        ->and($user->can('daily_closing.execute'))->toBeTrue();
});

it('can deactivate user', function () {
    $action = app(CreateUserAction::class);
    $deactivate = app(DeactivateUserAction::class);

    $user = $action->execute([
        'name' => 'Andi Salesman',
        'email' => 'andi@falaya.id',
        'password' => 'password',
        'role' => 'SALESMAN',
    ]);

    $deactivate->execute($user);

    expect($user->fresh()->is_active)->toBeFalse();
});

it('spatie role and column role are in sync after creation', function () {
    $action = app(CreateUserAction::class);

    $user = $action->execute([
        'name' => 'Owner Test',
        'email' => 'owner@falaya.id',
        'password' => 'password',
        'role' => 'OWNER',
    ]);

    expect($user->role)->toBe('OWNER')
        ->and($user->getRoleNames()->first())->toBe('OWNER');
});
