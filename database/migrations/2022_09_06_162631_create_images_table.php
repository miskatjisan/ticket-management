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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedInteger('category_id')->default(0);
            $table->string('image_name', 40)->nullable();
            $table->string('track_id')->nullable();
            $table->string('title', 40)->nullable();
            $table->text('description')->nullable();
            $table->text('tags')->nullable();
            $table->decimal('price', 28, 8)->default(0);
            $table->unsignedTinyInteger('is_free')->default(1);
            $table->unsignedTinyInteger('attribution')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
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
        Schema::dropIfExists('images');
    }
};
