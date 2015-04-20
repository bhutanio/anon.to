<?php namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __construct()
    {
        $hash = Str::random(6);
        dump($hash);
    }
}
