<?php

namespace App\Services;

use App\Models\Link;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AnonServices
{
    /**
     * @param $hash
     * @return \App\Models\Link
     */
    public function getLink($hash)
    {
        if ($link = Cache::get('links:' . $hash)) {
            return $this->hydrateLink($link);
        }

        $link = Link::where('hash', $hash)->firstOrFail();
        return $this->cachedLink($link);
    }

    public function getUrl($hash)
    {
        return $this->getLink($hash)->url;
    }

    public function findLink($url)
    {
        if (!is_array($url)) {
            $url = $this->parseUrl($url);
        }

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

        return $link->first();
    }

    public function parseUrl($url)
    {
        $parsed = parse_url($url);
        if (!empty($parsed['path']) && $parsed['path'] == '/') {
            $parsed['path'] = null;
        }

        return $parsed + [
                "scheme" => null,
                "host" => null,
                "port" => null,
                "user" => null,
                "pass" => null,
                "path" => null,
                "query" => null,
                "fragment" => null,
            ];
    }

    public function unParseUrl($parsed)
    {
        if ($parsed instanceof Link) {
            $parsed = [
                "scheme" => $parsed->url_scheme,
                "host" => $parsed->url_host,
                "port" => $parsed->url_port,
                "path" => $parsed->url_path,
                "query" => $parsed->url_query,
                "fragment" => $parsed->url_fragment,
            ];
        }

        if (empty($parsed['path'])) {
            $parsed['path'] = '/';
        }

        $unparsed = $parsed['scheme'] . '://' . $parsed['host'];
        $unparsed .= !empty($parsed['port']) ? ':' . $parsed['port'] : '';
        $unparsed .= $parsed['path'];
        $unparsed .= !empty($parsed['query']) ? '?' . $parsed['query'] : '';
        $unparsed .= !empty($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        return $unparsed;
    }

    public function addLink($url)
    {
        if (!is_array($url)) {
            $url = $this->parseUrl($url);
        }

        if ($link = $this->findLink($url)) {
            return $this->cachedLink($link);
        }

        $link = Link::create([
            'hash' => $this->uniqueHash(),
            'url_scheme' => $url['scheme'],
            'url_host' => $url['host'],
            'url_port' => $url['port'],
            'url_path' => $url['path'],
            'url_query' => $url['query'],
            'url_fragment' => $url['fragment'],
        ]);

        return $this->cachedLink($link);
    }

    /**
     * @param $url
     * @return \Illuminate\Http\Response
     */
    public function redirect($url)
    {
        $hash = null;
        if (is_valid_url($url)) {
            $url = urldecode($url);
            $url = html_entity_decode($url);
        } else {
            $hash = $url;
            $url = $this->getUrl($url);
        }

        if ($this->denyRedirect($url)) {
            abort(403, 'Redirect Denied. Link Blocked!');
        }

        app('redis')->lpush('redirects', $hash ?? $url);

        return response()->view('anonymous', ['url' => $url, 'allow_redirect' => $this->allowRedirect($url)])
            ->setExpires(Carbon::now()->addHours(1))
            ->header('Cache-Control', 'public,max-age=' . (3600) . ',s-maxage=' . (3600));
    }

    private function uniqueHash()
    {
        $hash = $this->generateHash();
        $link = Link::where('hash', $hash)->first();
        while ($link) {
            $hash = $this->generateHash();
            $link = Link::where('hash', $hash)->first();
        }

        return $hash;
    }

    private function hydrateLink($link)
    {
        return (Link::hydrate([$link]))->first();
    }

    private function generateHash()
    {
        $hash = Str::random(6);
        while (in_array(strtolower($hash), $this->excludedWords()->toArray())) {
            $hash = Str::random(6);
        }

        return $hash;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function excludedWords()
    {
        return collect(json_decode(File::get(storage_path('json/words-six.json')), true));
    }

    private function cachedLink(Link $link)
    {
        Cache::put('links:' . $link->hash, $link->toArray(), 86400);
        return $link;
    }

    private function allowRedirect($url)
    {
        $parsed = $this->parseUrl($url);

        foreach ((array)Cache::get('allowlist') as $host) {
            if (Str::endsWith($parsed['host'], $host)) {
                return true;
            }
        }

        return false;
    }

    private function denyRedirect($url)
    {
        $parsed = $this->parseUrl($url);

        foreach ((array)Cache::get('denylist') as $host) {
            if (Str::endsWith($parsed['host'], $host)) {
                return true;
            }
        }

        return false;
    }
}
