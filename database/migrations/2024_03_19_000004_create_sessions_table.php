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
        Schema::create('sessions', function (Blueprint $table) {
            // Laravel's default session fields
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            
            // Added the 7 standard fields (all nullable for session table)
            $table->string('CompanyCode', 20)->nullable();
            $table->tinyInteger('Status')->nullable();
            $table->tinyInteger('IsDeleted')->nullable();
            $table->string('CreatedBy', 32)->nullable();
            $table->dateTime('CreatedDate')->nullable();
            $table->string('LastUpdatedBy', 32)->nullable();
            $table->dateTime('LastUpdatedDate')->nullable();
            
            // Note: No timestamps() as sessions table has last_activity instead
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};