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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('showroom_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('name');
            $table->string('contact_person');
            $table->string('mobile');
            $table->string('address');
            $table->decimal('initial_balance', 15, 2);
            $table->enum('status', ['Receivable', 'Payable']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
