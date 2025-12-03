<?php

namespace Tests\Feature;

use Tests\TestCase;

class ImageGenerationTest extends TestCase
{
    /**
     * Test that image generation status endpoint returns successful response.
     */
    public function test_image_status_endpoint_returns_successful_response(): void
    {
        $response = $this->get('/image/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'image_generation_available',
            'gd_version',
            'supported_formats' => [
                'png',
                'jpeg',
                'gif',
                'webp',
            ],
        ]);
    }

    /**
     * Test that image generation endpoint returns successful response when GD is available.
     */
    public function test_image_generate_endpoint_returns_image(): void
    {
        // Skip this test if GD is not available
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available');
        }

        $response = $this->get('/image/generate?text=Test&width=100&height=50');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
    }

    /**
     * Test that image generation with custom parameters works.
     */
    public function test_image_generate_with_custom_parameters(): void
    {
        // Skip this test if GD is not available
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available');
        }

        $response = $this->get('/image/generate?text=Hello&width=200&height=100&bg_color=ff0000&text_color=ffffff');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
    }

    /**
     * Test that image dimensions are constrained.
     */
    public function test_image_dimensions_are_constrained(): void
    {
        // Skip this test if GD is not available
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available');
        }

        // Test that extremely large dimensions don't cause issues
        $response = $this->get('/image/generate?width=10000&height=10000');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
    }
}
