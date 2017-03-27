<?php

namespace App\Http\Controllers;

class ShortenLinkController extends LinksController
{

    public function __construct()
    {
        parent::__construct();
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

        if ($link = $this->urlExists($parsed_url)) {
            return response()->json(['url' => url($link->hash)], 200);
        }

        $hash = $this->createUrlHash($parsed_url);

        return response()->json(['url' => url($hash)], 200);
    }
}