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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_category_id');
            $table->string('question');
            $table->string('answer');
            $table->boolean('enabled')->default(1);
            $table->integer('position');

            /*

                id int NOT NULL
                question text NOT NULL
                answer text NOT NULL
                FK
                category_id bigint NOT NULL
                enabled tinyint(1) NOT NULL
                created_at timestamp
                updated_at timestamp
                position int NOT NULL

            */
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
