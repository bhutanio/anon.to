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
        $redirect = null;
        if ($requests) {
            $requests = collect($requests);

            $url_key = $requests->keys()->first() ? str_replace('_', '.', $requests->keys()->first()) : null;
            $url_value = $requests->first();

            if ($this->validateURL($url_key)) {
                $redirect = $url_key;
            } elseif ($this->validateURL($url_value)) {
                $redirect = $url_value;
            }
        }
        if ($redirect) {
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
