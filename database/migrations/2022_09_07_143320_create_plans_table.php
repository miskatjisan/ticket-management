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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->nullable();
            $table->string('title', 255)->nullable();
            $table->decimal('monthly_price', 28, 8)->default(0);
            $table->decimal('yearly_price', 28, 8)->default(0);
            $table->integer('daily_limit')->default(0);
            $table->integer('monthly_limit')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
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
        Schema::dropIfExists('plans');
    }
};
