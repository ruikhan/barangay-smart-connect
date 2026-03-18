<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('municipality');
            $table->string('province');
            $table->string('region')->default('CALABARZON');
            $table->string('slug')->unique();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('barangay_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('barangays')
                  ->nullOnDelete();
            $table->string('role')
                  ->default('resident')
                  ->after('barangay_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['barangay_id']);
            $table->dropColumn(['barangay_id', 'role']);
        });

        Schema::dropIfExists('barangays');
    }
};