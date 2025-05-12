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
        Schema::create('components_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); //<related_model>_id Laravel default template for ids
            $table->string('name');
            $table->longText('description');
            $table->longText('javascript');
            $table->longText('css');
            $table->longText('html');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_components_user');
    }
};
