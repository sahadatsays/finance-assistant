<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->string('type', 32)->default('custom')->after('name');
        });

        Schema::create('goal_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('notes')->nullable();
            $table->dateTime('contributed_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['goal_id', 'contributed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_contributions');

        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
