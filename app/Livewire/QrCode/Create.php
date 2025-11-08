<?php

declare(strict_types=1);

namespace App\Livewire\QrCode;

use App\Actions\QrCode\GenerateQrCode;
use App\Livewire\Concerns\HasRateLimiting;
use Livewire\Component;

class Create extends Component
{
    use HasRateLimiting;

    public string $content = '';

    public ?string $qrCodeDataUrl = null;

    public ?string $errorMessage = null;

    public string $lastGeneratedContent = '';

    /**
     * Generate a QR code with rate limiting.
     */
    public function generateQrCode(GenerateQrCode $generateQrCode): void
    {
        // Reset state
        $this->qrCodeDataUrl = null;
        $this->errorMessage = null;

        // Check rate limit (with IP hashing for privacy)
        if ($this->checkRateLimit('generate-qr', 50, 10, true)) {
            $seconds = $this->getRateLimitSeconds('generate-qr', true);
            $minutes = ceil($seconds / 60);
            $this->errorMessage = "Too many QR codes generated. Please try again in {$minutes} minutes.";

            return;
        }

        // Validate inputs
        $validated = $this->validate([
            'content' => [
                'required',
                'string',
                'max:2900',
            ],
        ], [
            'content.required' => 'Please enter content for your QR code.',
            'content.max' => 'Content cannot exceed 2,900 characters.',
        ]);

        try {
            // Generate PNG QR code for preview
            $pngData = $generateQrCode->execute($validated['content'], 'png');

            // Convert to base64 for preview
            $this->qrCodeDataUrl = 'data:image/png;base64,'.base64_encode($pngData);

            // Save content for downloads before clearing input
            $this->lastGeneratedContent = $validated['content'];

            // Hit the rate limiter (with IP hashing for privacy)
            $this->hitRateLimit('generate-qr', 3600, true);

            // Clear the input
            $this->reset(['content']);
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while generating the QR code. Please try again.';
        }
    }

    /**
     * Download QR code as PNG.
     */
    public function downloadPng(GenerateQrCode $generateQrCode): mixed
    {
        if (empty($this->content) && empty($this->qrCodeDataUrl)) {
            $this->errorMessage = 'Please enter content to generate a QR code.';

            return null;
        }

        try {
            $content = ! empty($this->content) ? $this->content : $this->extractContentFromDataUrl();
            $pngData = $generateQrCode->execute($content, 'png');
            $filename = 'qr-code-'.time().'.png';

            return response()->streamDownload(function () use ($pngData) {
                echo $pngData;
            }, $filename, [
                'Content-Type' => 'image/png',
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while downloading the QR code.';

            return null;
        }
    }

    /**
     * Download QR code as SVG.
     */
    public function downloadSvg(GenerateQrCode $generateQrCode): mixed
    {
        if (empty($this->content) && empty($this->qrCodeDataUrl)) {
            $this->errorMessage = 'Please enter content to generate a QR code.';

            return null;
        }

        try {
            $content = ! empty($this->content) ? $this->content : $this->extractContentFromDataUrl();
            $svgData = $generateQrCode->execute($content, 'svg');
            $filename = 'qr-code-'.time().'.svg';

            return response()->streamDownload(function () use ($svgData) {
                echo $svgData;
            }, $filename, [
                'Content-Type' => 'image/svg+xml',
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while downloading the QR code.';

            return null;
        }
    }

    /**
     * Download QR code as PDF.
     */
    public function downloadPdf(GenerateQrCode $generateQrCode): mixed
    {
        if (empty($this->content) && empty($this->qrCodeDataUrl)) {
            $this->errorMessage = 'Please enter content to generate a QR code.';

            return null;
        }

        try {
            $content = ! empty($this->content) ? $this->content : $this->extractContentFromDataUrl();
            $pdfData = $generateQrCode->execute($content, 'pdf');
            $filename = 'qr-code-'.time().'.pdf';

            return response()->streamDownload(function () use ($pdfData) {
                echo $pdfData;
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while downloading the QR code.';

            return null;
        }
    }

    /**
     * Extract content from the last generated QR code.
     */
    protected function extractContentFromDataUrl(): string
    {
        return $this->lastGeneratedContent ?: 'https://example.com';
    }

    public function render()
    {
        return view('livewire.qr-code.create')
            ->layout('components.layouts.guest');
    }
}
