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
        Schema::table('users', function (Blueprint $table) {
            // Social login fields
            $table->string('google_id')->nullable();
            $table->string('github_id')->nullable();
            
            // Add the 7 standard fields if they don't exist
            if (!Schema::hasColumn('users', 'CompanyCode')) {
                $table->string('CompanyCode', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'Status')) {
                $table->tinyInteger('Status')->default(1);
            }
            if (!Schema::hasColumn('users', 'IsDeleted')) {
                $table->tinyInteger('IsDeleted')->default(0);
            }
            if (!Schema::hasColumn('users', 'CreatedBy')) {
                $table->string('CreatedBy', 32)->nullable();
            }
            if (!Schema::hasColumn('users', 'CreatedDate')) {
                $table->dateTime('CreatedDate')->nullable();
            }
            if (!Schema::hasColumn('users', 'LastUpdatedBy')) {
                $table->string('LastUpdatedBy', 32)->nullable();
            }
            if (!Schema::hasColumn('users', 'LastUpdatedDate')) {
                $table->dateTime('LastUpdatedDate')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop social login fields
            $table->dropColumn(['google_id', 'github_id']);
            
            // Only drop the standard fields if they were added by this migration
            // Note: In practice, you might not want to drop these if they're used elsewhere
            if (Schema::hasColumn('users', 'CompanyCode')) {
                $table->dropColumn('CompanyCode');
            }
            // Repeat for other standard fields if needed
        });
    }
};