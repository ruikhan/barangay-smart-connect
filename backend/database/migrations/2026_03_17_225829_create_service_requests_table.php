<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('barangay_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            // clearance|residency|indigency|business_permit|good_moral|blotter
            $table->string('status')->default('pending');
            // pending|processing|approved|rejected|released
            $table->text('purpose');
            $table->string('reference_number')->unique();
            $table->text('remarks')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};