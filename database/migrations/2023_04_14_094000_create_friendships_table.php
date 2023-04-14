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
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('requested_to');
            $table->unsignedBigInteger('destroyed_by');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('requested_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('destroyed_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('requested_on');
            $table->timestamp('destroyed_on');
            $table->string('status');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
