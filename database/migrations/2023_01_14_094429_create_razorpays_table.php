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
        Schema::create('razorpays', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code_id');
            $table->integer('machine_id');
            $table->integer('beverage_id');
            $table->integer('amount');
            $table->string('qr_code_image');
            $table->tinyInteger('status');
            $table->longText('response');
            $table->tinyInteger('straw');
            $table->tinyInteger('lid');
            $table->Integer('sugar');
            $table->tinyInteger('ice');
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
        Schema::dropIfExists('razorpays');
    }
};
