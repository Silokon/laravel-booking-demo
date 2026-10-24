<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedInteger('price');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('masters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialty');
            $table->time('work_from')->default('10:00');
            $table->time('work_to')->default('20:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('master_service', function (Blueprint $table) {
            $table->foreignId('master_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['master_id', 'service_id']);
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('client_name');
            $table->string('client_phone', 20);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('new');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['master_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('master_service');
        Schema::dropIfExists('masters');
        Schema::dropIfExists('services');
    }
};
