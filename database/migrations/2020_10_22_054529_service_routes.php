<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ServiceRoutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_routes', function (Blueprint $table) {

            $table->increments('id');
            $table->uuid('uuid');
            $table->integer('service_id')->unsigned();

            $table->string('tags');
            $table->string('summary');
            $table->enum('type', ['GET', 'PUT', 'POST', 'PATCH', 'DELETE', 'ANY'])->default('ANY');
            $table->string('path');
            $table->text('params')->nullable();

            $table->enum('security', ['OAuth2', 'OpenId', 'BasicAuth', 'Public'])->default('Public');
            $table->string('produces')->default('application/json');
            $table->string('scope')->nullable();

            $table->index('service_id');
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_routes');
    }
}
