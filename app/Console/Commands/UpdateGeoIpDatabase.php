<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UpdateGeoIpDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geoip:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the GeoIP database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Updating GeoIP database...');

        $url = 'https://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz';
        $path = storage_path('app/geoip.mmdb.gz');

        $response = Http::withOptions(['sink' => $path])->get($url);

        if ($response->failed()) {
            $this->error('Failed to download GeoIP database.');
            return 1;
        }

        $this->info('Decompressing GeoIP database...');
        $gzipped = gzopen($path, 'rb');
        $unzipped = fopen(storage_path('app/geoip.mmdb'), 'wb');
        stream_copy_to_stream($gzipped, $unzipped);
        gzclose($gzipped);
        fclose($unzipped);

        unlink($path);

        $this->info('GeoIP database updated successfully.');
        return 0;
    }
}
