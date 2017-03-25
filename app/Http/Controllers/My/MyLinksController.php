<?php

namespace App\Http\Controllers\My;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Services\UrlServices;
use Illuminate\Support\Facades\Auth;

class MyLinksController extends Controller
{

    /**
     * @var UrlServices
     */
    private $url_services;

    public function __construct(UrlServices $url_service)
    {
        parent::__construct();
        $this->url_services = $url_service;
    }

    public function index()
    {
        $links = Link::orderBy('created_at', 'asc');

        if(Auth::id()==2 && $this->request->is('admin')) {
            meta()->setMeta('Links Admin');
            $links->with('user');
        } else {
            meta()->setMeta('My Links');
            $links->where('created_by', Auth::id());
        }

        if ($hash = $this->request->get('hash')) {
            $links->where('hash', $hash);
        }

        if ($domain = $this->request->get('domain')) {
            $links->where('url_host', 'LIKE', '%'.$domain.'%');
        }

        $links = $links->paginate(50);

        $links->each(function ($item) {
            $item->full_url = $this->url_services->unParseUrlFromDb($item);
        });

        return view('my.links', compact('links'));
    }
}
