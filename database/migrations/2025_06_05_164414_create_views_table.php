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
        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id'); // or post_id if you're keeping that naming
            $table->unsignedBigInteger('user_id')->nullable(); // null for guests
            $table->string('ip_address')->nullable(); // for unauthenticated users
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('components_user')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['post_id', 'user_id']); // prevent multiple views by same user
            $table->unique(['post_id', 'ip_address']); // prevent multiple views by same guest
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
