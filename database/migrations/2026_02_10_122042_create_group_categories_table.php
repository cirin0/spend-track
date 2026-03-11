<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();

            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_categories');
    }
};
