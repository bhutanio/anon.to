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

        if (!empty($url) && filter_var(urldecode($url), FILTER_VALIDATE_URL)) {
            return app(RedirectController::class)->anonymousRedirect($url);
        }

        meta()->setMeta(env('SITE_META_TITLE'));

        return view('home');
    }
}
