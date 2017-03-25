<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\UrlServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class RedirectController extends Controller
{
    /**
     * @var UrlServices
     */
    private $url_services;

    public function __construct(UrlServices $url_services)
    {
        parent::__construct();
        $this->url_services = $url_services;
    }

    public function redirect($key)
    {
        if ($url = Cache::get($key)) {
            return $this->anonymousRedirect(url($url));
        }

        $url = '/';
        try {
            $link = Link::where('hash', $key)->firstOrFail();
            if ($link) {
                $url = $this->url_services->unParseUrlFromDb($link);
                Cache::put($key, $url, 60 * 24);
            }
        } catch (ModelNotFoundException $e) {
            abort(404, 'Link not found!');
        }

        return $this->anonymousRedirect(url($url));
    }

    public function anonymousRedirect($url)
    {
        $url = urldecode($url);

        return response()->view('anonymous', compact('url'))
            ->setExpires(Carbon::now()->addHours(1))
            ->header('Cache-Control', 'public,max-age=' . (3600) . ',s-maxage=' . (3600));
    }
}
