<?php

namespace App\Services;

use App\Models\Links;

class UrlServices
{
    /**
     * @param $url
     * @return array
     */
    public function parseUrl($url)
    {
        $defaults = [
            "scheme"   => null,
            "host"     => null,
            "port"     => null,
            "user"     => null,
            "pass"     => null,
            "path"     => null,
            "query"    => null,
            "fragment" => null,
        ];
        $parsed = parse_url($url) + $defaults;
        if (!empty($parsed['path']) && $parsed['path'] == '/') {
            $parsed['path'] = null;
        }

        return $parsed;
    }

    /**
     * @param array $parsed
     * @return string
     */
    public function unParseUrl(array $parsed)
    {
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

    public function unParseUrlFromDb(Links $link)
    {
        $segments = [
            "scheme"   => $link->url_scheme,
            "host"     => $link->url_host,
            "port"     => $link->url_port,
            "path"     => $link->url_path,
            "query"    => $link->url_query,
            "fragment" => $link->url_fragment,
        ];

        return $this->unParseUrl($segments);
    }
}