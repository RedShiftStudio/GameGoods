<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('demo_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('reference')->unique();
            $t->string('kind'); // charge, refund, decline
            $t->decimal('amount', 12, 2);
            $t->timestamps();
            $t->index(['order_id', 'kind']);
        });
    }
    public function down(): void { Schema::dropIfExists('demo_payments'); }
};
