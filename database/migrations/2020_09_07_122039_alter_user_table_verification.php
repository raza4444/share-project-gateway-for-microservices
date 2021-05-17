<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AlterUserTableVerification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->integer('email_verification_token')->nullable()->after('email');
            $table->string('phone_number')->nullable()->after('email_verified_at');
            $table->timestamp('phone_number_verified_at')->nullable()->after('phone_number');
            $table->string('otp_assigned_user_id')->nullable()->after('phone_number');
            $table->integer('otp_retry_count')->after('phone_number')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('phone_number_verified_at');
            $table->dropColumn('email_verification_token');
            $table->dropColumn('otp_assigned_user_id');
            $table->dropColumn('phone_number');
            $table->dropColumn('otp_retry_count');
        });
    }
}
