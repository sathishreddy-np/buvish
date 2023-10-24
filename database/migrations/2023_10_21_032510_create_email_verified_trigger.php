<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('
            CREATE TRIGGER update_is_verified_in_users
            BEFORE UPDATE ON users
            FOR EACH ROW
            BEGIN
                IF NEW.email_verified_at IS NOT NULL THEN
                    SET NEW.is_verified = 1;
                ELSE
                    SET NEW.is_verified = 0;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_verified_trigger_in_users');
    }
};
