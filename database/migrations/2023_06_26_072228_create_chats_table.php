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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_name');
            $table->string('sender_agency_name')->nullable();
            $table->unsignedBigInteger('recepient_id');
            $table->string('recepient_name');
            $table->string('recepient_agency_name')->nullable();
            $table->string('sender_thumb')->nullable();
            $table->string('recepient_thumb')->nullable();
            $table->string('recent')->boolean();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
