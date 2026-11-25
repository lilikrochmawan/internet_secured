<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TagihanService
{
    private const UNPAID_STATUS = [0, '0', 'belum', '', 2, '2'];

    /**
     * Tagihan bulan depan hanya ditampilkan jika jatuh_tempo <= tanggal ini di bulan depan.
     * Contoh: jatuh_tempo 1-5 Desember saat ini masih November → tagihan Des ditampilkan.
     */
    private const EARLY_DUE_DAY_LIMIT = 5;

    public function getPelangganIdsByPhone(?string $noTelp): array
    {
        return Pelanggan::getIdsBySamePhone($noTelp);
    }

    public function applyUnpaidFilter(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereNull('status_bayar')
                ->orWhereIn('status_bayar', self::UNPAID_STATUS);
        });
    }

    /**
     * Cek apakah jatuh_tempo tagihan ini memenuhi syarat "early due"
     * (jatuh di tanggal 1-5 bulan depan sehingga boleh tampil di bulan ini).
     */
    private function isNextMonthEarlyDue(?string $jatuhTempo, string $nextMonthStr): bool
    {
        if (!$jatuhTempo) {
            return false;
        }
        try {
            $dt = Carbon::parse($jatuhTempo);
            // nextMonthStr format: 'mY' misal '122026' (m bisa 1 atau 2 digit)
            $nmMonth = (int) substr($nextMonthStr, 0, strlen($nextMonthStr) - 4);
            $nmYear  = (int) substr($nextMonthStr, -4);
            return $dt->year === $nmYear
                && $dt->month === $nmMonth
                && $dt->day <= self::EARLY_DUE_DAY_LIMIT;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Hitung target bulan ini dan bulan berikutnya dengan aman (menghindari bug akhir bulan tgl 29-31).
     *
     * @return array{0: string, 1: string} [targetMonth, nextMonthStr]
     */
    private function resolveMonthWindow(?string $currentMonth = null): array
    {
        if ($currentMonth && preg_match('/^\d{6}$/', $currentMonth)) {
            $baseDate = Carbon::createFromFormat('mY', $currentMonth)->startOfMonth();
        } else {
            $baseDate = Carbon::now()->startOfMonth();
        }

        return [
            $baseDate->format('mY'),
            $baseDate->copy()->addMonth()->format('mY'),
        ];
    }

    public function sumUnpaidBulanIni(array $pelangganIds, ?string $currentMonth = null): int
    {
        if ($pelangganIds === []) {
            return 0;
        }

        [$targetMonth, $nextMonthStr] = $this->resolveMonthWindow($currentMonth);

        // Ambil keduanya lalu filter early-due di PHP
        return (int) $this->applyUnpaidFilter(
            Tagihan::whereIn('id_pelanggan', $pelangganIds)
                ->whereIn('bulan_tahun', [$targetMonth, $nextMonthStr])
                ->where(function (Builder $query) {
                    $query->where('manual_invoice', 0)
                        ->orWhereNull('manual_invoice');
                })
        )
        ->get()
        ->filter(function (Tagihan $inv) use ($targetMonth, $nextMonthStr) {
            if ($inv->bulan_tahun === $targetMonth) return true;
            return $this->isNextMonthEarlyDue($inv->jatuh_tempo, $nextMonthStr);
        })
        ->sum(fn($inv) => max(0, ($inv->jml_bayar ?? 0) - ($inv->terbayar ?? 0)));
    }

    public function sumPiutang(array $pelangganIds): int
    {
        if ($pelangganIds === []) return 0;
        return (int) $this->applyUnpaidFilter(
            Tagihan::whereIn('id_pelanggan', $pelangganIds)->where('status_bayar', 2)
        )->sum(DB::raw('COALESCE(jml_bayar, 0) - COALESCE(terbayar, 0)'));
    }

    public function sumUnpaidManual(array $pelangganIds): int
    {
        if ($pelangganIds === []) {
            return 0;
        }

        return (int) $this->applyUnpaidFilter(
            Tagihan::whereIn('id_pelanggan', $pelangganIds)
                ->where('manual_invoice', 1)
        )->sum(DB::raw('COALESCE(jml_bayar, 0) - COALESCE(terbayar, 0)'));
    }

    public function getUnpaidInvoices(array $pelangganIds, ?string $currentMonth = null): Collection
    {
        if ($pelangganIds === []) {
            return collect();
        }

        [$targetMonth, $nextMonthStr] = $this->resolveMonthWindow($currentMonth);
        $allowedMonths = array_unique([$targetMonth, $nextMonthStr]);

        // Ambil tagihan reguler bulan ini & bulan depan, lalu filter early-due di PHP
        return $this->applyUnpaidFilter(
            Tagihan::with('pelanggan')
                ->whereIn('id_pelanggan', $pelangganIds)
                ->where(function (Builder $query) use ($allowedMonths) {
                    $query->where(function (Builder $q) use ($allowedMonths) {
                        // Tagihan reguler
                        $q->where(function (Builder $inner) {
                            $inner->where('manual_invoice', 0)
                                  ->orWhereNull('manual_invoice');
                        })
                        ->whereIn('bulan_tahun', $allowedMonths);
                    })
                    // Invoice manual: semua yang belum lunas
                    ->orWhere('manual_invoice', 1)
                    // Piutang (cicilan)
                    ->orWhere('status_bayar', 2);
                })
        )
            ->orderBy('id_pelanggan')
            ->orderBy('bulan_tahun')
            ->get()
            ->filter(function (Tagihan $invoice) use ($targetMonth, $nextMonthStr) {
                // Piutang & invoice manual: selalu tampilkan
                if ((int) $invoice->manual_invoice === 1 || (string) $invoice->status_bayar === '2') {
                    return true;
                }
                // Tagihan bulan ini: selalu tampilkan
                if ($invoice->bulan_tahun === $targetMonth) {
                    return true;
                }
                // Tagihan bulan depan: hanya jika jatuh_tempo di tanggal 1-5 bulan depan
                if ($invoice->bulan_tahun === $nextMonthStr) {
                    return $this->isNextMonthEarlyDue($invoice->jatuh_tempo, $nextMonthStr);
                }
                return false;
            })
            ->map(function (Tagihan $invoice) {
                $amount = (int) max(0, ($invoice->jml_bayar ?? 0) - ($invoice->terbayar ?? 0));
                $monthYear = $invoice->bulan_tahun;

                if (is_string($monthYear) && strlen($monthYear) === 6) {
                    $monthYear = substr($monthYear, 0, 2) . '/' . substr($monthYear, 2);
                }

                return [
                    'id'             => $invoice->id_tagihan,
                    'month_year'     => $monthYear,
                    'amount'         => $amount,
                    'item'           => $invoice->item_tagihan ?: 'Tagihan Internet',
                    'nama_pelanggan' => $invoice->pelanggan?->nama_pelanggan,
                    'manual_invoice' => (bool) $invoice->manual_invoice,
                ];
            })
            ->filter(fn($item) => $item['amount'] > 0)
            ->values();
    }
}
