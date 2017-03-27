<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('link_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('link_id')->unsigned();
            $table->index('link_id');
            $table->text('url');
            $table->string('email');
            $table->text('comment');
            $table->string('ip_address')->nullable();
            $table->integer('created_by')->unsigned();
            $table->index('created_by');
            $table->timestamps();
        });

        Schema::table('links', function (Blueprint $table) {
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex('links_created_by_index');
        });

        Schema::dropIfExists('link_reports');
    }
}
