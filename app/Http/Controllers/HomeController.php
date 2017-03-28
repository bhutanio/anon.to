<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $url = $this->request->server('QUERY_STRING');

        if (is_valid_url($url)) {
            return app(RedirectController::class)->anonymousRedirect($url);
        }

        meta()->setMeta(env('SITE_META_TITLE'));

        return view('home');
    }
}
