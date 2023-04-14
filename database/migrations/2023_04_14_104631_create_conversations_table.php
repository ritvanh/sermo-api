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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('started_by');
            $table->unsignedBigInteger('started_with');
            $table->foreign('started_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('started_with')->references('id')->on('users')->onDelete('cascade');
            $table->binary('is_active');
            $table->timestamp('started_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
