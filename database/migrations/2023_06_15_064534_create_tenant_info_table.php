<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_info', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->references('id')->on('tenants');
            $table->string('address_1', 255);
            $table->string('address_2', 255)->nullable();
            $table->string('address_3', 255)->nullable();
            $table->string('postcode', 255);
            $table->string('city', 255);
            $table->string('state', 255);
            $table->string('country', 255);
            $table->string('company', 255)->nullable();
            $table->string('phone_no', 255);
            $table->string('fax_no', 255);
            $table->string('website', 255);
            $table->text('email')->nullable();
            $table->bigInteger('rep_type_id');
            $table->string('rep_id_no', 255);
            $table->string('rep_name', 255);
            $table->string('rep_designation', 255);
            $table->string('rep_dept_unit', 255);
            $table->string('rep_email', 255);
            $table->string('rep_phone_no', 255);
            $table->string('rep_mobile_no', 255);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_info');
    }
};
