<?php

it('can connect to postgresql database', function () {
    expect(DB::connection()->getPdo())->not->toBeNull();
});

it('migrations table exists in testing database', function () {
    expect(DB::table('migrations')->count())->toBeGreaterThan(0);
});
