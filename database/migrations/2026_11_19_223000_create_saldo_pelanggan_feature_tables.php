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
        // 1. Tambah kolom saldo di tb_pelanggan jika belum ada
        if (!Schema::hasColumn('tb_pelanggan', 'saldo')) {
            Schema::table('tb_pelanggan', function (Blueprint $table) {
                if (Schema::hasColumn('tb_pelanggan', 'odp')) {
                    $table->bigInteger('saldo')->default(0)->after('odp');
                } else {
                    $table->bigInteger('saldo')->default(0);
                }
            });
        }

        // 2. Tambah kolom bayar_saldo & bayar_non_saldo di tb_tagihan jika belum ada
        Schema::table('tb_tagihan', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_tagihan', 'bayar_saldo')) {
                if (Schema::hasColumn('tb_tagihan', 'terbayar')) {
                    $table->bigInteger('bayar_saldo')->default(0)->after('terbayar');
                } else {
                    $table->bigInteger('bayar_saldo')->default(0);
                }
            }
            if (!Schema::hasColumn('tb_tagihan', 'bayar_non_saldo')) {
                if (Schema::hasColumn('tb_tagihan', 'bayar_saldo')) {
                    $table->bigInteger('bayar_non_saldo')->default(0)->after('bayar_saldo');
                } else {
                    $table->bigInteger('bayar_non_saldo')->default(0);
                }
            }
        });

        // 3. Tambah kolom is_saldo & bayar_saldo di tb_kas jika belum ada
        Schema::table('tb_kas', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_kas', 'is_saldo')) {
                if (Schema::hasColumn('tb_kas', 'jenis_kas')) {
                    $table->tinyInteger('is_saldo')->default(0)->after('jenis_kas');
                } else {
                    $table->tinyInteger('is_saldo')->default(0);
                }
            }
            if (!Schema::hasColumn('tb_kas', 'bayar_saldo')) {
                if (Schema::hasColumn('tb_kas', 'is_saldo')) {
                    $table->bigInteger('bayar_saldo')->default(0)->after('is_saldo');
                } else {
                    $table->bigInteger('bayar_saldo')->default(0);
                }
            }
        });

        // 4. Buat tabel tbl_saldo_mutasi untuk riwayat keluar masuk saldo per pelanggan
        if (!Schema::hasTable('tbl_saldo_mutasi')) {
            Schema::create('tbl_saldo_mutasi', function (Blueprint $table) {
                $table->id();
                $table->integer('id_pelanggan')->index();
                $table->enum('tipe', ['masuk', 'keluar']);
                $table->bigInteger('jumlah');
                $table->bigInteger('saldo_awal')->default(0);
                $table->bigInteger('saldo_akhir')->default(0);
                $table->string('kategori', 50)->default('topup_manual'); // topup_manual, topup_midtrans, bayar_tagihan, penyesuaian, refund
                $table->text('keterangan')->nullable();
                $table->string('reference_id', 100)->nullable()->index(); // order_id midtrans, id_tagihan, dll
                $table->integer('created_by')->nullable(); // user_id admin jika manual
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_saldo_mutasi');

        Schema::table('tb_kas', function (Blueprint $table) {
            if (Schema::hasColumn('tb_kas', 'is_saldo')) {
                $table->dropColumn('is_saldo');
            }
            if (Schema::hasColumn('tb_kas', 'bayar_saldo')) {
                $table->dropColumn('bayar_saldo');
            }
        });

        Schema::table('tb_tagihan', function (Blueprint $table) {
            if (Schema::hasColumn('tb_tagihan', 'bayar_saldo')) {
                $table->dropColumn('bayar_saldo');
            }
            if (Schema::hasColumn('tb_tagihan', 'bayar_non_saldo')) {
                $table->dropColumn('bayar_non_saldo');
            }
        });

        if (Schema::hasColumn('tb_pelanggan', 'saldo')) {
            Schema::table('tb_pelanggan', function (Blueprint $table) {
                $table->dropColumn('saldo');
            });
        }
    }
};
