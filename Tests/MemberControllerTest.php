<?php

use Faker\Factory as Faker;

// 创建一个全局的 $faker 变量
$faker = Faker::create();

it('can member register', function () use ($faker) {
    $password = $faker->password(6, 18);
    $response = $this->postJson('/api/v1/auth/register', [
        'type' => 'mail',
        'username' => $faker->email,
        'password' => $password,
        'password_confirmation' => $password,
        'code' => $faker->randomNumber(6),
        'agree' => 1,
        'way' => 1,
    ]);

    $response->assertStatus(200)->assertSee('access_token');
});

it('can member login', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'type' => 'mail',
        'username' => 'test@163.com',
        'password' => '123456',
        'agree' => 1,
        'way' => 1,
    ]);

    $response->assertStatus(200)->assertSee('access_token');
});

it('can member logout', function () {
    $token = auth('member')->attempt(['username' => 'test@163.com', 'password' => '123456']);

    $response = $this->getJson('/api/v1/auth/logout', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(200);
});

it('can member refresh', function () {
    $token = auth('member')->attempt(['username' => 'test@163.com', 'password' => '123456']);

    $response = $this->getJson('/api/v1/auth/refresh', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(200)->assertSee('access_token');
});

it('can send code', function () {
    $response = $this->postJson('/api/v1/auth/send_code', [
        'type' => 'mail',
        'username' => 'test@163.com',
        'agree' => 1,
        'way' => 2,
    ]);

    $response->assertStatus(200);
});

it('can member info', function () {
    $token = auth('member')->attempt(['username' => 'test@163.com', 'password' => '123456']);

    $response = $this->getJson('/api/v1/member/info', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(200);
});

it('can member update password', function () {
    $token = auth('member')->attempt(['username' => 'test@163.com', 'password' => '123456']);

    $response = $this->postJson('/api/v1/member/update_password', [
        'old_password' => '123456',
        'new_password' => '1234567',
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(200);
});
