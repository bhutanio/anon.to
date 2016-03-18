<?php

namespace App\Http\Controllers;

use App\Services\MetaDataService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Cache\RedisStore
     */
    protected $cache;

    /**
     * @var MetaDataService
     */
    protected $meta;

    public function __construct()
    {
        $this->request = app('request');
        $this->cache = app('cache');
        $this->meta = app(MetaDataService::class);
    }
}
