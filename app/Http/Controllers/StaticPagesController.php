<?php

namespace App\Http\Controllers;

use App\Models\Contents;
use App\Services\UrlServices;

class StaticPagesController extends Controller
{
    private $url_service;

    public function __construct(UrlServices $url_service)
    {
        parent::__construct();
        $this->url_service = $url_service;
    }

    public function about()
    {
        $content = $this->loadCmsContent('about', 'About');

        return view('static', compact('content'));
    }

    public function terms()
    {
        $content = $this->loadCmsContent('terms', 'Terms of Service');

        return view('static', compact('content'));
    }

    public function privacy()
    {
        $content = $this->loadCmsContent('privacy-policy', 'Privacy Policy');

        return view('static', compact('content'));
    }

    public function unsubscribe()
    {
        meta()->setMeta('Unsubscribe');

        $content = '';

        // Add your code to unsubscribe from your mailing list

        flash('You have been successfully unsubscribed from ' . env('SITE_NAME'), 'success');

        return view('static', compact('content'));
    }

    private function loadCmsContent($slug, $title)
    {
        meta()->setMeta($title);
        $content = '';
        if ($cms = Contents::where('title_slug', $slug)->first()) {
            meta()->setMeta($cms->title);
            $content = $cms->content;
        }

        return $content;
    }
}