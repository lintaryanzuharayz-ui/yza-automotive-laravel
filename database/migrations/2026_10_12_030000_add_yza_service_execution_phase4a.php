<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_findings', function (Blueprint $table): void {
            $table->id('id_service_finding');
            $table->foreignId('id_servis')->constrained('servis', 'id_servis')->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description');
            $table->text('recommendation')->nullable();
            $table->string('severity', 30)->default('info');
            $table->string('status', 30)->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['id_servis', 'status']);
        });

        Schema::create('service_approval_requests', function (Blueprint $table): void {
            $table->id('id_service_approval_request');
            $table->foreignId('id_servis')->constrained('servis', 'id_servis')->cascadeOnDelete();
            $table->unsignedBigInteger('id_service_finding')->nullable();
            $table->string('title', 160);
            $table->text('reason');
            $table->string('status', 30)->default('pending');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('customer_note')->nullable();
            $table->timestamps();
            $table->index(['id_servis', 'status']);
        });

        Schema::create('service_approval_request_items', function (Blueprint $table): void {
            $table->id('id_service_approval_request_item');
            $table->foreignId('approval_request_id')->constrained('service_approval_requests', 'id_service_approval_request')->cascadeOnDelete();
            $table->string('item_type', 30);
            $table->string('description', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price_snapshot', 15, 2);
            $table->decimal('total_snapshot', 15, 2);
            $table->unsignedBigInteger('id_jenis_servis')->nullable();
            $table->unsignedBigInteger('id_sparepart')->nullable();
            $table->unsignedBigInteger('applied_detail_id')->nullable();
            $table->unsignedBigInteger('applied_sparepart_id')->nullable();
            $table->timestamps();
            $table->index(['approval_request_id', 'item_type'], 'idx_approval_items_type');
        });

        Schema::create('service_execution_events', function (Blueprint $table): void {
            $table->id('id_service_execution_event');
            $table->foreignId('id_servis')->constrained('servis', 'id_servis')->cascadeOnDelete();
            $table->string('event_key', 60);
            $table->string('label_snapshot', 160);
            $table->string('actor_type', 40)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->index(['id_servis', 'event_key']);
        });

        Schema::create('service_quality_controls', function (Blueprint $table): void {
            $table->id('id_service_quality_control');
            $table->foreignId('id_servis')->unique()->constrained('servis', 'id_servis')->cascadeOnDelete();
            $table->string('status', 30)->default('not_started');
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('service_quality_control_items', function (Blueprint $table): void {
            $table->id('id_service_quality_control_item');
            $table->foreignId('quality_control_id')->constrained('service_quality_controls', 'id_service_quality_control')->cascadeOnDelete();
            $table->string('item_key', 80);
            $table->string('label_snapshot', 180);
            $table->boolean('is_mandatory')->default(true);
            $table->string('result', 10)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['quality_control_id', 'item_key'], 'uq_service_qc_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_quality_control_items');
        Schema::dropIfExists('service_quality_controls');
        Schema::dropIfExists('service_execution_events');
        Schema::dropIfExists('service_approval_request_items');
        Schema::dropIfExists('service_approval_requests');
        Schema::dropIfExists('service_findings');
    }
};
