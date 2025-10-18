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
        Schema::create('blog_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->longText('content');
            $table->decimal('average_score', 4, 2)->nullable();
            $table->integer('providers_used')->default(0);
            $table->json('results')->nullable();
            $table->json('consensus_issues')->nullable();
            $table->json('consensus_strengths')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_evaluations');
    }
};
