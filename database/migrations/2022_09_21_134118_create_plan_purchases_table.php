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
        Schema::create('plan_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedInteger('plan_id')->default(0);
            $table->text('plan_detail')->nullable();
            $table->unsignedInteger('remaining_daily_limit')->default(0);
            $table->unsignedInteger('remaining_monthly_limit')->default(0);
            $table->string('trx', 40)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('expired_at')->nullable();
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
        Schema::dropIfExists('plan_purchases');
    }
};
