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
        Schema::create('engagements', function (Blueprint $table) {
             $table->id();
 
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('bid_id')->nullable();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('client_id');

            $table->decimal('agreed_amount', 10, 2)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('state', 20)->default('active')->index(); // active/completed/cancelled

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('engagements');
    }
};
