<?php

it('health check endpoint returns 200', function () {
    $response = $this->get('/up');
    $response->assertStatus(200);
});
