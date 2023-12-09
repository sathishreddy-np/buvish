<?php

use App\Models\Activity;
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
        Schema::create('booking_timings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Activity::class);
            $table->string('day',55);
            $table->integer('start_time');
            $table->integer('end_time');
            $table->integer('no_of_slots')->default(0);
            $table->json('allowed_categories'); //"gender","age_from","age_to", "price"
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_timings');
    }
};
