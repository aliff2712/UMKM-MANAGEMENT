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
        Schema::create('insight_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->enum('type', [
                'cashflow',     // analisis arus kas
                'slow_moving',  // produk lambat terjual
                'peak_day',     // hari/jam penjualan tertinggi
                'margin_drop',  // penurunan margin
                'top_product',  // produk terlaris
                'expense_spike' // lonjakan pengeluaran
            ]);
            $table->string('title');
            $table->text('message'); // teks insight yang akan di-render dengan efek typing
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->date('period_start'); // periode data yang dianalisis
            $table->date('period_end');
            $table->json('metadata')->nullable(); // raw data pendukung insight
            // contoh metadata cashflow:
            // { "total_income": 5000000, "total_expense": 6000000, "ratio": 0.83 }
            $table->boolean('is_dismissed')->default(false); // user bisa dismiss insight
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insight_logs');
    }
};
