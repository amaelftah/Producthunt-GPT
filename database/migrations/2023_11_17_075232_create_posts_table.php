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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('product_hunt_id')->index();
            $table->string('title');
            $table->text('description');
            $table->string('url');
            $table->unsignedBigInteger('votes');
            $table->timestamp('featured_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->json('topics')->nullable();
            $table->json('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
