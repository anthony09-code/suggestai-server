<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("students", function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->string("google_id")->unique();
            $table->string("name");
            $table->string("email")->unique();
            $table->text("profile_picture")->nullable();
            $table->string("google_token")->nullable();
            $table->string("google_refresh_token")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("students");
    }
};
