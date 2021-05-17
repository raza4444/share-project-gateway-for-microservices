<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterServiceRoutesTableAddParentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_routes', function (Blueprint $table) {
            $table->unsignedInteger('parent_id')->nullable()->index()->after('uuid');
            $table->foreign('parent_id')->references('id')->on('service_routes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_routes', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
}
