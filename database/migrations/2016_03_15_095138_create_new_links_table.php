<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNewLinksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('hash')->unique();
            $table->string('url_scheme');
            $table->text('url_host');
            $table->text('url_port')->nullable()->default(null);
            $table->text('url_path')->nullable()->default(null);
            $table->text('url_query')->nullable()->default(null);
            $table->text('url_fragment')->nullable()->default(null);
            $table->integer('created_by')->nullable()->default(null)->unsigned();
            $table->timestamps();
        });

        if (!empty(DB::select("SHOW TABLES LIKE '_old_links'"))) {
            $urls = \DB::select("SELECT * FROM _old_links");
            foreach ($urls as $url) {
                $parsed = parse_url($url->url) + [
                        "scheme"   => null,
                        "host"     => null,
                        "port"     => null,
                        "user"     => null,
                        "pass"     => null,
                        "path"     => null,
                        "query"    => null,
                        "fragment" => null,
                    ];
                $parsed['path'] = ($parsed['path'] == '/') ? null : $parsed['path'];
                try {
                    \DB::table('links')->insert([
                        'hash'         => $url->hash,
                        'url_scheme'   => $parsed['scheme'],
                        'url_host'     => $parsed['host'],
                        'url_port'     => $parsed['port'],
                        'url_path'     => $parsed['path'],
                        'url_query'    => $parsed['query'],
                        'url_fragment' => $parsed['fragment'],
                        'created_by'   => 1,
                    ]);
                } catch (\Exception $e) {
                    dump($e->getMessage());
                    dump($url->url);
                }
            }
            Schema::drop('_old_links');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('links');
    }
}
