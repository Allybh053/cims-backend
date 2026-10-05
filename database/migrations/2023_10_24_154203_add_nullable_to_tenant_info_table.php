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
        Schema::table('tenant_info', function (Blueprint $table) {
            $table->bigInteger('rep_type_id')->nullable()->change();
            $table->string('rep_id_no', 255)->nullable()->change();
            $table->string('rep_name', 255)->nullable()->change();
            $table->string('rep_designation', 255)->nullable()->change();
            $table->string('rep_dept_unit', 255)->nullable()->change();
            $table->string('rep_email', 255)->nullable()->change();
            $table->string('rep_phone_no', 255)->nullable()->change();
            $table->string('rep_mobile_no', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_info', function (Blueprint $table) {
            $table->bigInteger('rep_type_id');
            $table->string('rep_id_no', 255);
            $table->string('rep_name', 255);
            $table->string('rep_designation', 255);
            $table->string('rep_dept_unit', 255);
            $table->string('rep_email', 255);
            $table->string('rep_phone_no', 255);
            $table->string('rep_mobile_no', 255);
        });
    }
};
