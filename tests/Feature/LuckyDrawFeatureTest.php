<?php

namespace Tests\Feature;

use Tests\TestCase;

class LuckyDrawFeatureTest extends TestCase
{
    public function test_dashboard_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Lucky Draw');
    }

    public function test_spinwheel_page_loads_successfully(): void
    {
        $response = $this->get('/spinwheel');
        $response->assertStatus(200);
        $response->assertSee('Spin Wheel');
    }

    public function test_dice_page_loads_successfully(): void
    {
        $response = $this->get('/dice');
        $response->assertStatus(200);
        $response->assertSee('Dice (1 Item Chooser)');
    }

    public function test_balancer_page_loads_successfully(): void
    {
        $response = $this->get('/balancer');
        $response->assertStatus(200);
        $response->assertSee('The Balancer (Total Pool Capped)');
    }

    public function test_unbalancer_page_loads_successfully(): void
    {
        $response = $this->get('/unbalancer');
        $response->assertStatus(200);
        $response->assertSee('The Unbalancer (1 - 10,000 Draw)');
    }

    public function test_balancer_api_distributes_within_max_pool(): void
    {
        $response = $this->postJson('/api/balancer/draw', [
            'max_pool' => 10000,
            'remaining_pool' => 10000,
            'drawer_name' => 'Alice',
            'expected_drawers' => 10,
            'draw_count' => 0,
            'distribution' => 'red_envelope'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'drawer_name',
            'amount',
            'remaining_pool',
            'max_pool',
            'percentage_of_pool',
            'draw_number'
        ]);
        $this->assertTrue($response->json('amount') > 0);
        $this->assertTrue($response->json('remaining_pool') < 10000);
    }

    public function test_unbalancer_api_generates_probabilistic_reward(): void
    {
        $response = $this->postJson('/api/unbalancer/draw', [
            'max' => 10000,
            'drawer_name' => 'Bob',
            'model' => 'tiered',
            'draw_count' => 0
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'drawer_name',
            'amount',
            'max',
            'tier',
            'percentage_of_max',
            'draw_number'
        ]);
        $this->assertTrue($response->json('amount') >= 1);
        $this->assertTrue($response->json('amount') <= 10000);
    }
}
