<?php

it('can create user with owner role', function () {
    $user = actingAsRole('OWNER');
    expect($user->hasRole('OWNER'))->toBeTrue();
});

it('can create user with admin role', function () {
    $user = actingAsRole('ADMIN');
    expect($user->hasRole('ADMIN'))->toBeTrue();
});

it('can create user with salesman role', function () {
    $user = actingAsRole('SALESMAN');
    expect($user->hasRole('SALESMAN'))->toBeTrue();
});

it('owner has correct permissions', function () {
    $user = actingAsRole('OWNER');
    expect($user->can('customer.approve'))->toBeTrue();
    expect($user->can('customer.create'))->toBeFalse();
});

it('salesman has correct permissions', function () {
    $user = actingAsRole('SALESMAN');
    expect($user->can('customer.create'))->toBeTrue();
    expect($user->can('customer.approve'))->toBeFalse();
});
