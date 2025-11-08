<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Analytics\RecordVisit;
use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Services\CacheKeyService;
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
    public function show(Request $request, string $hash)
    {
        // Try to get link from cache first, fallback to database
        $link = Cache::remember(
            CacheKeyService::forLink($hash),
            config('anon.default_cache_ttl', 86400),
            fn () => Link::where('hash', $hash)->first()
        );

        // Handle not found
        if (! $link) {
            abort(404, 'Link not found');
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
}
