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
        Schema::table('engagements', function (Blueprint $table) {
            // projekat (kad se projekat obriše, briši i engagement)
            $table->foreign('project_id')
                  ->references('id')->on('projects')
                  ->cascadeOnDelete();

            // bid može biti NULL; ako se bid obriše, postavi NULL
            $table->foreign('bid_id')
                  ->references('id')->on('bids')
                  ->nullOnDelete();

            // provider i client su users; briši kaskadno
            $table->foreign('provider_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();

            $table->foreign('client_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('engagements', function (Blueprint $table) {
             $table->dropForeign(['project_id']);
            $table->dropForeign(['bid_id']);
            $table->dropForeign(['provider_id']);
            $table->dropForeign(['client_id']);
        });
    }
};
