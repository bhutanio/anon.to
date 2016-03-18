<?php

namespace App\Services;

class MetaDataService
{
    protected $meta_title, $page_title, $description, $canonical, $icon, $theme, $color;

    public function __construct()
    {
        $this->meta_title = env('SITE_NAME');
        $this->setDefaultMeta();
    }

    public function setMeta($page_title = null, $meta_title = null, $description = null, $icon = null)
    {
        $this->pageTitle($page_title);
        $this->metaTitle($meta_title);
        if (empty($meta_title)) {
            $this->metaTitle($page_title.' - '.env('SITE_NAME'));
        }
        $this->description($description);
        $this->icon($icon);
    }

    public function setTheme($theme = null, $color = null)
    {
    }

    public function metaTitle($title = null)
    {
        if ($title) {
            $this->meta_title = $title;
        }

        return $this->meta_title;
    }

    public function pageTitle($title = null)
    {
        if ($title) {
            $this->page_title = $title;
        }

        return $this->page_title;
    }

    public function description($description = null)
    {
        if ($description) {
            $this->description = $description;
        }

        return $this->description;
    }

    public function canonical($url = null)
    {
        if ($url) {
            $this->canonical = $url;
        }

        return $this->canonical;
    }

    public function icon($icon = null)
    {
        if ($icon) {
            $this->icon = $icon;
        }

        return $this->icon;
    }

    private function setDefaultMeta()
    {
        switch (request()->getRequestUri()) {
            case '/auth/login':
                $this->setMeta('Login');
                break;
            case '/auth/register':
                $this->setMeta('Register');
                break;
        }
    }
}
