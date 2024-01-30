<?php

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
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string("title", 250);
            $table->string("description", 2000);
            $table->string("tags", 2000);
            $table->string("attachment", 255);
            $table->boolean("broadcasted")->default(false);
            $table->boolean("reportable")->default(true);
            $table->string("message_data", 20000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
