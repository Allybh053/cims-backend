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
            $table->unsignedBigInteger('receiver_id')->nullable()->change();
            $table->unsignedBigInteger('approver_id')->nullable()->change();
            $table->date('approver_date')->nullable()->change();
            $table->bigInteger('reject_reason')->nullable()->change();
            $table->string('reject_remark')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('receiver_id');
            $table->unsignedBigInteger('approver_id');
            $table->date('approver_date');
            $table->bigInteger('reject_reason');
            $table->string('reject_remark');
        });
    }
};
