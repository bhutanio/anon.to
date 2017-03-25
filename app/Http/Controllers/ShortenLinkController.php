<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\UrlServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShortenLinkController extends Controller
{
    /**
     * @var UrlServices
     */
    private $url_service;

    public function __construct(UrlServices $url_service)
    {
        parent::__construct();
        $this->url_service = $url_service;
    }

    public function shorten()
    {
        $this->validate($this->request, [
            'url' => 'required|url',
        ], [
            'url.required' => 'Please paste a link to shorten',
            'url.url'      => 'Link must be a valid url starting with http:// or https://',
        ]);

        $url = $this->request->get('url');
        $parsed_url = $this->url_service->parseUrl($url);

        if ($parsed_url['host'] == parse_url(env('APP_URL'), PHP_URL_HOST)) {
            return response()->json(['url' => url($url)], 200);
        }

        if ($hash = $this->urlExists($parsed_url)) {
            return response()->json(['url' => url($hash)], 200);
        }

        $hash = $this->createUrlHash($parsed_url);

        return response()->json(['url' => url($hash)], 200);
    }

    private function urlExists(array $url)
    {
        $link = Link::where('url_scheme', $url['scheme'])->where('url_host', $url['host']);

        if (!empty($url['port'])) {
            $link = $link->where('url_port', $url['port']);
        } else {
            $link = $link->whereNull('url_port');
        }

        if (!empty($url['path'])) {
            $link = $link->where('url_path', $url['path']);
        } else {
            $link = $link->whereNull('url_path');
        }

        if (!empty($url['query'])) {
            $link = $link->where('url_query', $url['query']);
        } else {
            $link = $link->whereNull('url_query');
        }

        if (!empty($url['fragment'])) {
            $link = $link->where('url_fragment', $url['fragment']);
        } else {
            $link = $link->whereNull('url_fragment');
        }

        $link = $link->first();
        if ($link) {
            return $link->hash;
        }

        return false;
    }

    private function createUrlHash($parsed_url)
    {
        $hash = $this->generateHash();

        $link = Link::where('hash', $hash)->first();
        while ($link) {
            $hash = $this->generateHash();
            $link = Link::where('hash', $hash)->first();
        }

        $link = Link::create([
            'hash'         => $hash,
            'url_scheme'   => $parsed_url['scheme'],
            'url_host'     => $parsed_url['host'],
            'url_port'     => $parsed_url['port'],
            'url_path'     => $parsed_url['path'],
            'url_query'    => $parsed_url['query'],
            'url_fragment' => $parsed_url['fragment'],
            'created_by'   => Auth::check() ? Auth::id() : 1,
        ]);

        $this->cacheLink($link);

        return $hash;
    }

    private function cacheLink($link)
    {
        Cache::put($link->hash, $this->url_service->unParseUrlFromDb($link), 60 * 24);

        return $link;
    }

    private function generateHash()
    {
        $hash = Str::random(6);
        while (in_array(strtolower($hash), excluded_words())) {
            $hash = Str::random(6);
        }

        return $hash;
    }
}