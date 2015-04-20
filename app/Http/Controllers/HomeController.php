<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request, $hash = null)
    {
        $requests = $request->all();
        if ($request->getQueryString()) {
            $redirect = urldecode($request->getQueryString());
//            dump($redirect);
            return view('redirect', compact('redirect'));
        }

        return view('home');
    }

    private function validateURL($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        }

        return false;
    }

}
