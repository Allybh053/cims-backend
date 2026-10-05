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
        Schema::table('inquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->after('user_id')->nullable();
            $table->dateTime('respond_date')->after('answer')->nullable();
            $table->softDeletes();

            $table->dropColumn('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->text('title')->after('user_id')->nullable();
            $table->dropColumn('admin_id');
            $table->dropColumn('respond_date');
            $table->dropSoftDeletes();
        });
    }
};
