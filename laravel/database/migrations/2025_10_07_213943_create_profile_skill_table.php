<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profile_skill', function (Blueprint $table) {
            $table->foreignId('profile_id')
                  ->constrained('profiles')
                  ->cascadeOnDelete();

            $table->foreignId('skill_id')
                  ->constrained('skills')
                  ->cascadeOnDelete();

            $table->unique(['profile_id', 'skill_id']); // jedan skill po profilu samo jednom
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profile_skill');
    }
};
