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
        Schema::create('partytransactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_at');
            $table->string('showroom_id');
            $table->string('party_code');
            $table->string('relation');
            $table->decimal('credit', 10, 2);
            $table->decimal('debit', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->string('transaction_method');
            $table->string('transaction_type', 100);
            $table->longText('remark')->nullable();
            $table->string('transaction_by');
            $table->string('paid_by')->nullable();
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partytransactions');
    }
};
