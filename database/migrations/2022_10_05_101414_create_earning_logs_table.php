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
        Schema::create('earning_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('contributor_id')->default(0);
            $table->unsignedInteger('image_id')->default(0);
            $table->decimal('amount')->default(0);
            $table->decimal('date')->nullable();
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
        Schema::dropIfExists('earning_logs');
    }
};
