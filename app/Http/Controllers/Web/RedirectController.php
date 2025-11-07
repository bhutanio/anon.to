<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Analytics\RecordVisit;
use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Services\UrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedirectController extends Controller
{
    public function __construct(
        protected UrlService $urlService,
        protected RecordVisit $recordVisit,
    ) {}

    /**
     * Show the anonymous redirect warning page.
     */
    public function show(Request $request, string $hashOrSlug)
    {
        // Try to get link from cache first, fallback to database
        $link = Cache::remember(
            "link:{$hashOrSlug}",
            config('anon.default_cache_ttl', 86400),
            fn () => Link::where('hash', $hashOrSlug)
                ->orWhere('slug', $hashOrSlug)
                ->first()
        );

        // Handle not found
        if (! $link) {
            abort(404, 'Link not found');
        }

        // Check if expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(410, 'This link has expired');
        }

        // Check if active
        if (! $link->is_active) {
            abort(403, 'This link has been disabled');
        }

        // Record the visit (async in future with queues)
        $this->recordVisit->execute($link);

        // Reconstruct the full destination URL
        $destinationUrl = $this->urlService->reconstruct($link);

        // Parse URL for display on warning page
        $parsed = $this->urlService->parse($destinationUrl);

        // Return Livewire/Volt component view
        return view('livewire.redirect', [
            'link' => $link,
            'destinationUrl' => $destinationUrl,
            'parsed' => $parsed,
        ]);
    }

    /**
     * Perform the actual redirect (called when user clicks "Continue").
     *
     * This method can be called via a route or handled in the Livewire component.
     * For now, we'll handle it in the Livewire component with a simple redirect.
     */
    public function redirect(Request $request, string $hashOrSlug)
    {
        $link = Link::where('hash', $hashOrSlug)
            ->orWhere('slug', $hashOrSlug)
            ->firstOrFail();

        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(410, 'This link has expired');
        }

        if (! $link->is_active) {
            abort(403, 'This link has been disabled');
        }

        $destinationUrl = $this->urlService->reconstruct($link);

        return redirect()->away($destinationUrl);
    }
}
