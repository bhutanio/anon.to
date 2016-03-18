<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\UrlServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RedirectController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function redirect(UrlServices $url_services, $key)
    {
        if ($url = $this->cache->get($key)) {
            return $this->anonymousRedirect(url($url));
        }

        $url = '/';
        try {
            $link = Link::where('hash', $key)->firstOrFail();
            if ($link) {
                $url = $url_services->unParseUrlFromDb($link);
                $this->cache->put($key, $url, 60 * 24);
            }
        } catch (ModelNotFoundException $e) {
            abort(404, 'Link not found!');
        }

        return $this->anonymousRedirect(url($url));
    }

    public function anonymousRedirect($url)
    {
        return response()->view('anonymous', compact('url'))
            ->setExpires(Carbon::now()->addDays(30))
            ->header('Cache-Control', 'public,max-age=' . (3600 * 24 * 30) . ',s-maxage=' . (3600 * 24 * 30));
    }
}
