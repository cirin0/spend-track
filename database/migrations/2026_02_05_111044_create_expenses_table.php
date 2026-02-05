<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('category_id');
            $table->enum('category_type', ['default', 'user']);
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['category_id', 'category_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
