<?php

use Illuminate\Support\Facades\Redis;

it('can connect to redis', function () {
    $result = Redis::ping();
    expect($result)->toBeTrue();
});

it('redis is using database index 2', function () {
    $index = config('database.redis.default.database');
    expect((int) $index)->toBe(2);
});
