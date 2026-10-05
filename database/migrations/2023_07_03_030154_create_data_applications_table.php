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
        Schema::create('data_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_application_status_id');
            $table->string('requestor_agency_id');
            $table->foreign('requestor_agency_id')->references('id')->on('tenants');

            $table->unsignedBigInteger('requestor_id');
            $table->string('receiver_agency_id');
            $table->foreign('receiver_agency_id')->references('id')->on('tenants');
            $table->unsignedBigInteger('receiver_id');
            $table->json('request_body');
            $table->json('response_body')->nullable();
            $table->string('poi_name');
            $table->string('poi_ic');
            $table->unsignedBigInteger('approver_id');
            $table->date('approver_date');
            $table->bigInteger('reject_reason');
            $table->string('reject_remark');
            $table->string('references_no');
//            $table->string('title');
//            $table->text('description');
//            $table->enum('approval_status', ['pending', 'approved']);
//            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_applications');
    }
};
