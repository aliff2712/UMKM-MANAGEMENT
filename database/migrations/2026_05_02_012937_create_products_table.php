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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('unit', 50)->default('pcs'); // pcs, kg, liter, dll
            $table->decimal('purchase_price', 15, 2)->default(0); // HPP / harga beli
            $table->decimal('selling_price', 15, 2)->default(0);  // harga jual
            $table->integer('stock_qty')->default(0);
            $table->integer('stock_minimum')->default(0); // trigger notifikasi stok tipis
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
