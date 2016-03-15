<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->getQueryString()) {
            $redirect = urldecode($request->server('QUERY_STRING'));

            //return view('redirect', compact('redirect'));
            return response(view('redirect', compact('redirect')))->setExpires(Carbon::now()->addDays(30))
            ->header('Cache-Control', 'public,max-age='.(1800).',s-maxage='.(1800));
        }

        return view('home');
    }

    /**
     * @param $hash
     *
     * @return \Illuminate\View\View
     */
    public function hash($hash)
    {
        $link = Link::where('hash', $hash)->first();

        if ($link) {
            $redirect = urldecode($link->url);

            return view('redirect', compact('redirect'));
        }

        abort(404);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function shorten(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|url',
        ]);

        $url = $this->cleanUpUrl($request);

        // If URL exists, redirect immediately
        $link = Link::where('url', $url)->first();
        if ($link) {
            return redirect(route('home'))
                    ->with('hash', $link->hash);
        }

        $link = $this->createUrlHash($url);

        return redirect(route('home'))
            ->with('hash', $link->hash);
    }

    /**
     * @param Request $request
     *
     * @return mixed|string
     */
    private function cleanUpUrl(Request $request)
    {
        $url = $request->get('url');

        $url = trim(rtrim($url, '?'));
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * @param $url
     *
     * @return static
     */
    private function createUrlHash($url)
    {
        $hash = Str::random(6);
        $link = Link::where('hash', $hash)->first();

        while ($link) {
            $hash = Str::random(6);
            $link = Link::where('hash', $hash)->first();
        }

        $link = Link::create([
            'url' => $url,
            'hash' => $hash,
        ]);

        return $link;
    }
}
