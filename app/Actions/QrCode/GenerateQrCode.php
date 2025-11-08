<?php

declare(strict_types=1);

namespace App\Actions\QrCode;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;

class GenerateQrCode
{
    /**
     * Generate a QR code in the specified format.
     *
     * @param  string  $content  The content to encode in the QR code
     * @param  string  $format  The output format (png, svg, pdf)
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function execute(string $content, string $format = 'png'): string
    {
        // Step 1: Validate content
        $this->validateContent($content);

        // Step 2: Generate QR code based on format
        return match ($format) {
            'png' => $this->generatePng($content),
            'svg' => $this->generateSvg($content),
            'pdf' => $this->generatePdf($content),
            default => throw new \InvalidArgumentException("Invalid format: {$format}"),
        };
    }

    /**
     * Validate QR code content.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateContent(string $content): void
    {
        // Required field validation
        if (empty(trim($content))) {
            throw new \InvalidArgumentException('Content is required.');
        }

        // Character limit: 2,900 characters (industry standard for Medium error correction)
        if (mb_strlen($content) > 2900) {
            throw new \InvalidArgumentException('Content cannot exceed 2,900 characters.');
        }
    }

    /**
     * Generate PNG QR code.
     */
    protected function generatePng(string $content): string
    {
        $options = new QROptions([
            'version' => QRCode::VERSION_AUTO, // Auto-detect version based on content
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_M, // Medium error correction (15%)
            'scale' => 16, // Scale to get approximately 512x512px
            'imageBase64' => false,
        ]);

        return (new QRCode($options))->render($content);
    }

    /**
     * Generate SVG QR code.
     */
    protected function generateSvg(string $content): string
    {
        $options = new QROptions([
            'version' => QRCode::VERSION_AUTO, // Auto-detect version based on content
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_M, // Medium error correction (15%)
            'svgViewBoxSize' => 512,
            'imageBase64' => false, // Output raw SVG
        ]);

        return (new QRCode($options))->render($content);
    }

    /**
     * Generate PDF QR code.
     */
    protected function generatePdf(string $content): string
    {
        // Step 1: Generate PNG QR code
        $pngData = $this->generatePng($content);

        // Step 2: Convert PNG to base64 for embedding
        $base64Image = base64_encode($pngData);

        // Step 3: Create HTML with centered QR code
        $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body {
                        margin: 0;
                        padding: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                    }
                    img {
                        width: 512px;
                        height: 512px;
                    }
                </style>
            </head>
            <body>
                <img src="data:image/png;base64,'.$base64Image.'" alt="QR Code">
            </body>
            </html>
        ';

        // Step 4: Generate PDF using dompdf
        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
