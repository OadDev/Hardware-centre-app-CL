<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_no')->unique();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['Pending', 'In Transit', 'Received', 'Partially Received', 'Rejected'])->default('Pending');
            $table->text('notes')->nullable();
            $table->date('expected_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_shipments');
    }
};
