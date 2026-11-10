<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name', 80); $t->string('email')->unique(); $t->string('password');
            $t->text('bio')->nullable(); $t->boolean('is_admin')->default(false); $t->boolean('is_banned')->default(false);
            $t->rememberToken(); $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete(); $t->timestamps();
        });
        Schema::create('listings', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->foreignId('category_id')->constrained();
            $t->string('title'); $t->text('description'); $t->decimal('price', 12, 2); $t->string('game')->nullable();
            $t->string('kind')->default('item'); $t->string('image')->nullable(); $t->string('status')->default('active');
            $t->unsignedInteger('stock')->default(1); $t->timestamps(); $t->index(['status', 'category_id']);
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id(); $t->foreignId('listing_id')->constrained()->restrictOnDelete();
            $t->foreignId('buyer_id')->constrained('users'); $t->foreignId('seller_id')->constrained('users');
            $t->decimal('price', 12, 2); $t->string('status')->default('awaiting_delivery');
            $t->text('delivery_note')->nullable(); $t->timestamp('delivered_at')->nullable(); $t->timestamp('completed_at')->nullable(); $t->timestamps();
        });
        Schema::create('messages', function (Blueprint $t) {
            $t->id(); $t->foreignId('sender_id')->constrained('users'); $t->foreignId('recipient_id')->constrained('users');
            $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); $t->text('body'); $t->timestamp('read_at')->nullable(); $t->timestamps();
            $t->index(['recipient_id', 'sender_id']);
        });
        Schema::create('reviews', function (Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $t->foreignId('author_id')->constrained('users'); $t->foreignId('seller_id')->constrained('users');
            $t->unsignedTinyInteger('rating'); $t->text('body')->nullable(); $t->timestamps();
        });
        Schema::create('disputes', function (Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $t->foreignId('opened_by')->constrained('users'); $t->text('reason'); $t->text('resolution')->nullable();
            $t->string('status')->default('open'); $t->timestamps();
        });
        Schema::create('favorites', function (Blueprint $t) {
            $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $t->primary(['user_id', 'listing_id']);
        });
    }

    public function down(): void
    {
        foreach (['favorites', 'disputes', 'reviews', 'messages', 'orders', 'listings', 'categories', 'users'] as $table) Schema::dropIfExists($table);
    }
};
