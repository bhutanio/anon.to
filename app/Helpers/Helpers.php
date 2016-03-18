<?php

/**
 * @return \App\Services\MetaDataService
 */
function meta()
{
    return app(App\Services\MetaDataService::class);
}

function asset_version()
{
    if (!empty($version = env('ASSET_VERSION'))) {
        return $version;
    }

    try {
        $version = file_get_contents(base_path('version.txt'));
        $version = trim($version);
        putenv('ASSET_VERSION=' . $version);

        return $version;
    } catch (\Exception $e) {
    }

    return date('Ymd');
}
