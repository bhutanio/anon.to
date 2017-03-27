<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RedirectController extends LinksController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function redirect($key)
    {
        if ($url = Cache::get($key)) {
            return $this->anonymousRedirect(url($url));
        }

        if ($link = $this->hashExists($key)) {
            $this->cacheLink($link);
            $url = $this->url_service->unParseUrlFromDb($link);

            return $this->anonymousRedirect(url($url));
        }

        return abort(404, 'Link not found!');
    }

    public function anonymousRedirect($url)
    {
        $url = urldecode($url);

        return response()->view('anonymous', compact('url'))
            ->setExpires(Carbon::now()->addHours(1))
            ->header('Cache-Control', 'public,max-age=' . (3600) . ',s-maxage=' . (3600));
    }
}
