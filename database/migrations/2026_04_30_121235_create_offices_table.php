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
        Schema::create("offices", function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table->string("office_name");
            $table->string("description")->nullable();
            $table->string("qr_code")->nullable();
            $table->string("access_link")->unique();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("offices");
    }
};
