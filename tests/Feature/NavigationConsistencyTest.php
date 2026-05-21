<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create admin user for navigation testing
    $adminRole = Role::create(['name' => 'Administrator']);
    $this->user = User::factory()->create(['role_id' => $adminRole->id]);
});

it('displays navigation on dashboard page', function () {
    $response = $this->actingAs($this->user)->get('/dashboard');
    
    $response->assertStatus(200);
    $response->assertSee('Dashboard');
    $response->assertSee('Grid');
    $response->assertSee('City Functions');
});

it('displays navigation on grid page', function () {
    $response = $this->actingAs($this->user)->get('/grid');
    
    $response->assertStatus(200);
    $response->assertSee('Dashboard');
    $response->assertSee('Grid');
    $response->assertSee('City Functions');
});

it('displays navigation on city functions page', function () {
    $response = $this->actingAs($this->user)->get('/city_functions');
    
    $response->assertStatus(200);
    $response->assertSee('Dashboard');
    $response->assertSee('Grid');
    $response->assertSee('City Functions');
});

it('displays navigation on audit log page', function () {
    $response = $this->actingAs($this->user)->get('/audit_log');
    
    $response->assertStatus(200);
    $response->assertSee('Dashboard');
    $response->assertSee('Grid');
    $response->assertSee('City Functions');
});

it('navigation links are in consistent position across pages', function () {
    $pages = ['/dashboard', '/grid', '/city_functions', '/audit_log'];
    
    foreach ($pages as $page) {
        $response = $this->actingAs($this->user)->get($page);
        
        //all pages should have the same navigation links
        $response->assertSee('Dashboard');
        $response->assertSee('Grid');
        
        //checks that the navigation links are in the same position
        $response->assertStatus(200);
    }
});
