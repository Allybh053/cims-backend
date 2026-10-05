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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roles_id');
            $table->unsignedBigInteger('parent_id');
            $table->string('title');
            $table->text('slug');
            $table->string('icon');
            $table->text('route_name');
            $table->string('layout');
            $table->bigInteger('lvl');
            $table->integer('position');
//            $table->text('description')->nullable();
//            $table->enum('status', ['enable', 'disabled']);
//            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
