<?php

use App\Models\User;

test('non-admin cannot access user management page', function () {
    $member = User::factory()->create(['role' => 'member']);

    $response = $this->actingAs($member)->get(route('admin.users.index'));

    $response->assertForbidden();
});

test('admin can view user management page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertOk();
});

test('admin can add a new user directly from the panel', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Budi Marcom',
        'email' => 'budi@srgroup.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'member',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Budi Marcom',
        'email' => 'budi@srgroup.test',
        'role' => 'member',
        'is_active' => true,
    ]);

    $newUser = User::where('email', 'budi@srgroup.test')->first();
    expect($newUser->email_verified_at)->not->toBeNull();
    expect(\Illuminate\Support\Facades\Hash::check('password123', $newUser->password))->toBeTrue();
});

test('new user created by admin can immediately login and reach dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Siti Marcom',
        'email' => 'siti@srgroup.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'member',
    ]);

    $response = $this->post('/login', [
        'email' => 'siti@srgroup.test',
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();

    $dashboard = $this->get('/dashboard');
    $dashboard->assertOk();
});

test('adding a user fails validation with duplicate email', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['email' => 'dupe@srgroup.test']);

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Duplikat',
        'email' => 'dupe@srgroup.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'member',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseCount('users', 2); // admin + existing dupe user only
});
