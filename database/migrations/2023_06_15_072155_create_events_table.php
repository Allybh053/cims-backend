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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamp('reminder_time')->nullable();
            $table->timestamps();
            $table->boolean('enabled')->default(true);
            $table->integer('repeat_type')->nullable();
            $table->integer('repeat_every')->nullable();
            $table->integer('repeat_on_sunday')->nullable();
            $table->integer('repeat_on_monday')->nullable();
            $table->integer('repeat_on_tuesday')->nullable();
            $table->integer('repeat_on_wednesday')->nullable();
            $table->integer('repeat_on_thursday')->nullable();
            $table->integer('repeat_on_friday')->nullable();
            $table->integer('repeat_on_saturday')->nullable();
            $table->integer('repeat_ends_type')->nullable();
            $table->integer('repeat_ends_occurrence')->nullable();
            $table->timestamp('repeat_ends_date')->nullable();
            $table->integer('alarm_enabled')->nullable();


            // $table->unsignedBigInteger('user_id');
            // $table->foreign('user_id')
            //     ->references('id')
            //     ->on('users')
            //     ->onDelete('cascade');

            // $table->string('title');
            // $table->timestamp('started_at')->nullable();
            // $table->timestamp('ended_at')->nullable();
            // $table->string('description')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
