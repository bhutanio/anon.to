<?php

namespace App\Http\Controllers;

use App\Models\Links;
use App\Services\UrlServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LinksController extends Controller
{
    /**
     * @var UrlServices
     */
    protected $url_service;

    public function __construct()
    {
        parent::__construct();
        $this->url_service = app(UrlServices::class);
    }

    protected function hashExists($hash)
    {
        return Links::where('hash', $hash)->first();
    }

    protected function urlExists(array $url)
    {
        $link = Links::where('url_scheme', $url['scheme'])->where('url_host', $url['host']);

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

        return $link->first();
    }

    protected function createUrlHash($parsed_url)
    {
        $hash = $this->generateHash();

        $link = Links::where('hash', $hash)->first();
        while ($link) {
            $hash = $this->generateHash();
            $link = Links::where('hash', $hash)->first();
        }

        $link = Links::create([
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

    protected function cacheLink($link)
    {
        Cache::put($link->hash, $this->url_service->unParseUrlFromDb($link), 60 * 24);

        return $link;
    }

    protected function generateHash()
    {
        $hash = Str::random(6);
        while (in_array(strtolower($hash), excluded_words())) {
            $hash = Str::random(6);
        }

        return $hash;
    }
}