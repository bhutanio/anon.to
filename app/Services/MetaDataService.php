<?php

namespace App\Services;

use Illuminate\Http\Request;

class MetaDataService
{
    protected $meta_title, $page_title, $description, $canonical, $icon, $theme, $color, $request;

    /**
     * MetaDataService constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->meta_title = $this->getDefaultTitle();
        $this->setDefaultMeta();
    }

    public function setMeta($page_title = null, $meta_title = null, $description = null, $icon = null)
    {
        $this->pageTitle($page_title);
        $this->metaTitle($meta_title);
        if (empty($meta_title)) {
            if ($page = $this->request->get('page')) {
                if ($page > 1) {
                    $page_title .= ' (Page ' . $page . ')';
                }
            }

            $this->metaTitle($page_title . ' - ' . $this->meta_title);
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
//        switch ($this->request->getRequestUri()) {
//            case '/login':
//                $this->setMeta('Login');
//                break;
//            case '/register':
//                $this->setMeta('Register');
//                break;
//            case '/password/reset':
//                $this->setMeta('Reset Password');
//                break;
//        }
    }

    /**
     * @return mixed
     */
    private function getDefaultTitle()
    {
        return env('SITE_NAME') ?: 'Site Name';
    }
}
