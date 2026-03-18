<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('barangay_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category');
            // flood|fire|accident|crime|infrastructure|health|other
            $table->string('severity')->default('moderate');
            // low|moderate|high|critical
            $table->decimal('latitude',  10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('address')->nullable();
            $table->string('status')->default('open');
            // open|acknowledged|resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};