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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->enum('type', [
                'low_stock',        // stok produk menipis
                'cashflow_warning', // pengeluaran > pemasukan
                'slow_moving',      // produk tidak terjual dalam X hari
                'margin_drop',      // margin produk turun signifikan
                'general'           // notifikasi umum
            ]);
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // meta data pendukung, misal: { "product_id": 5, "stock_qty": 2 }
            $table->boolean('is_read')->default(false);
            $table->enum('target_role', ['owner', 'admin', 'all'])->default('all');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifiactions');
    }
};
