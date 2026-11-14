<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_awards', function (Blueprint $table): void {
            $table->id('id_sales_award');
            $table->unsignedSmallInteger('award_year');
            $table->string('status', 20)->default('provisional');
            $table->string('rule_version', 40)->default('units-target-achievement-value-v1');
            $table->json('method_snapshot')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->unique('award_year');
        });

        Schema::create('sales_award_results', function (Blueprint $table): void {
            $table->id('id_sales_award_result');
            $table->foreignId('id_sales_award')->constrained('sales_awards', 'id_sales_award')->cascadeOnDelete();
            $table->foreignId('id_sales')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->unsignedInteger('ranking')->nullable();
            $table->unsignedInteger('final_units')->default(0);
            $table->unsignedInteger('target_units')->nullable();
            $table->decimal('achievement_percentage', 8, 2)->nullable();
            $table->decimal('final_amount', 15, 2)->default(0);
            $table->string('eligibility_status', 40)->default('eligible');
            $table->string('tie_group', 40)->nullable();
            $table->timestamps();
            $table->unique(['id_sales_award', 'id_sales']);
            $table->index(['id_sales_award', 'ranking']);
        });

        Schema::create('sales_award_audits', function (Blueprint $table): void {
            $table->id('id_sales_award_audit');
            $table->foreignId('id_sales_award')->constrained('sales_awards', 'id_sales_award')->cascadeOnDelete();
            $table->foreignId('actor_petugas_id')->nullable()->constrained('petugas', 'id_petugas')->nullOnDelete();
            $table->string('action', 40);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['id_sales_award', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_award_audits');
        Schema::dropIfExists('sales_award_results');
        Schema::dropIfExists('sales_awards');
    }
};
