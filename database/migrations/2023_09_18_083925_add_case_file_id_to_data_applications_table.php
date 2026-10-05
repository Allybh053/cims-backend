<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('case_file_id')->after('data_application_status_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_applications', function (Blueprint $table) {
            $table->dropColumn('case_file_id');
        });
    }
};
