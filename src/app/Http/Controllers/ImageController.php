<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    /**
     * Generate a simple image with the specified text.
     *
     * @param Request $request
     * @return Response
     */
    public function generate(Request $request): Response
    {
        $text = $request->input('text', 'Hello, World!');
        $width = min(max((int) $request->input('width', 400), 50), 2000);
        $height = min(max((int) $request->input('height', 200), 50), 2000);
        $bgColor = $this->hexToRgb($request->input('bg_color', '#3490dc'));
        $textColor = $this->hexToRgb($request->input('text_color', '#ffffff'));

        // Check if GD library is available
        if (!extension_loaded('gd')) {
            return response('GD library is not installed', 500);
        }

        // Create image
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            return response('Failed to create image', 500);
        }

        // Allocate colors
        $bg = imagecolorallocate($image, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        $fg = imagecolorallocate($image, $textColor['r'], $textColor['g'], $textColor['b']);

        if ($bg === false || $fg === false) {
            imagedestroy($image);
            return response('Failed to allocate colors', 500);
        }

        // Fill background
        imagefill($image, 0, 0, $bg);

        // Calculate text position (centered)
        $fontSize = 5; // Built-in font size (1-5)
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;

        // Draw text
        imagestring($image, $fontSize, (int) $x, (int) $y, $text, $fg);

        // Output image
        ob_start();
        $pngResult = imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        if ($pngResult === false || $imageData === false || $imageData === '') {
            return response('Failed to generate PNG image', 500);
        }

        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="generated.png"',
        ]);
    }

    /**
     * Check if image generation is available.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(): \Illuminate\Http\JsonResponse
    {
        $gdAvailable = extension_loaded('gd');
        $gdInfo = $gdAvailable ? gd_info() : [];

        return response()->json([
            'image_generation_available' => $gdAvailable,
            'gd_version' => $gdInfo['GD Version'] ?? 'Not available',
            'supported_formats' => [
                'png' => isset($gdInfo['PNG Support']) && $gdInfo['PNG Support'],
                'jpeg' => isset($gdInfo['JPEG Support']) && $gdInfo['JPEG Support'],
                'gif' => isset($gdInfo['GIF Create Support']) && $gdInfo['GIF Create Support'],
                'webp' => isset($gdInfo['WebP Support']) && $gdInfo['WebP Support'],
            ],
        ]);
    }

    /**
     * Convert hex color to RGB array.
     *
     * @param string $hex
     * @return array{r: int, g: int, b: int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            // Default to white if invalid
            return ['r' => 255, 'g' => 255, 'b' => 255];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
