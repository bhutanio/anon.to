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
        if (!empty($url)) {
            return app(RedirectController::class)->anonymousRedirect($url);
        }

        $this->meta->setMeta(env('SITE_META_TITLE'));

        return response(view('home'));
    }
}
