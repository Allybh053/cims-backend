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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('branch_name', 255);
            $table->bigInteger('type_id');
            $table->string('id_no', 255)->unique();
            $table->date('dob');
            $table->bigInteger('gender');
            $table->string('designation', 255);
            $table->string('dep_unit', 255);
            $table->string('phone_no', 255);
            $table->string('mobile_no', 255);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
