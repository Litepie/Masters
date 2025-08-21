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
        Schema::create('master_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_type_id')->constrained('master_types')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('code')->nullable();
            $table->string('iso_code')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('additional_data')->nullable();
            $table->json('meta_data')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['master_type_id', 'tenant_id', 'is_active']);
            $table->index(['parent_id', 'sort_order']);
            $table->index(['slug', 'master_type_id']);
            $table->index(['code', 'master_type_id']);
            $table->foreign('parent_id')->references('id')->on('master_data')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data');
    }
};
