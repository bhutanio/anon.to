<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InstallOrUpdateDatabaseTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        try {
            DB::unprepared('RENAME TABLE `jobs` TO `_old_jobs`;');
            DB::unprepared('RENAME TABLE `links` TO `_old_links`;');
        } catch (\Exception $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        try {
            DB::unprepared('RENAME TABLE `_old_jobs` TO `jobs`;');
            DB::unprepared('RENAME TABLE `_old_links` TO `links`;');
        } catch (\Exception $e) {
        }
    }
}
