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
        Schema::table('search_logs', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('description');
            $table->dropColumn('status');
            $table->json('request_body')->after('user_id');
            $table->json('response_body')->after('request_body')->nullable();
            $table->string('agency_id')->after('response_body')->nullable();
            $table->binary('results')->after('agency_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('search_logs', function (Blueprint $table) {
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['processing', 'ready', 'cancelled'])->nullable();
            $table->dropColumn('agency_id');
            $table->dropColumn('request_body');
            $table->dropColumn('response_body');
            $table->dropColumn('results');
        });
    }
};
