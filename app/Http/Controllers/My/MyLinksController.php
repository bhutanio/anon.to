<?php

namespace App\Http\Controllers\My;

use App\Http\Controllers\Controller;
use App\Models\Links;
use App\Services\UrlServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
        $links = Links::latest();

        if (Auth::id() == 2 && $this->request->is('admin/*')) {
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
            $links->where('url_host', 'LIKE', '%' . $domain . '%');
        }

        if ($path = $this->request->get('path')) {
            $links->where('url_path', 'LIKE', '%' . $path . '%');
        }

        $links = $links->paginate(50);

        $links->each(function ($item) {
            $item->full_url = $this->url_services->unParseUrlFromDb($item);
        });

        return view('my.links', compact('links'));
    }

    public function delete()
    {
        if (!Auth::check() || Auth::id() == 1) {
            return response()->json('Access Denied!', 403);
        }

        $id = (int)$this->request->get('id');
        if (empty($id)) {
            return response()->json('Invalid ID!', 422);
        }

        $link = Links::findOrFail($id);

        if (Auth::id() == 2) {
            Cache::forget($link->hash);
            $link->delete();

            return response()->json('Link Deleted Successfully!', 200);
        } else {
            return response()->json('Access Denied!', 403);
        }
    }
}
