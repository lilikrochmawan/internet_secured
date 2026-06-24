<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TagihanService
{
    private const UNPAID_STATUS = [0, '0', 'belum', ''];

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

    public function sumUnpaidBulanIni(array $pelangganIds, ?string $currentMonth = null): int
    {
        if ($pelangganIds === []) {
            return 0;
        }

        $targetMonth = $currentMonth ?: date('mY');

        return (int) $this->applyUnpaidFilter(
            Tagihan::whereIn('id_pelanggan', $pelangganIds)
                ->where('bulan_tahun', $targetMonth)
                ->where(function (Builder $query) {
                    $query->where('manual_invoice', 0)
                        ->orWhereNull('manual_invoice');
                })
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

        $targetMonth = $currentMonth ?: date('mY');

        // Ambil tagihan reguler bulan target + semua invoice manual belum lunas
        return $this->applyUnpaidFilter(
            Tagihan::with('pelanggan')
                ->whereIn('id_pelanggan', $pelangganIds)
                ->where(function (Builder $query) use ($targetMonth) {
                    // Tagihan reguler: hanya untuk bulan ini
                    $query->where(function (Builder $q) use ($targetMonth) {
                        $q->where(function (Builder $inner) {
                            $inner->where('manual_invoice', 0)
                                  ->orWhereNull('manual_invoice');
                        })
                        ->where('bulan_tahun', $targetMonth);
                    })
                    // Invoice manual: semua yang belum lunas
                    ->orWhere('manual_invoice', 1);
                })
        )
            ->orderBy('id_pelanggan')
            ->orderBy('bulan_tahun')
            ->get()
            ->map(function (Tagihan $invoice) {
                $amount = (int) max(0, ($invoice->jml_bayar ?? 0) - ($invoice->terbayar ?? 0));
                $monthYear = $invoice->bulan_tahun;

                if (is_string($monthYear) && strlen($monthYear) === 6) {
                    $monthYear = substr($monthYear, 0, 2) . '/' . substr($monthYear, 2);
                }

                return [
                    'id' => $invoice->id_tagihan,
                    'month_year' => $monthYear,
                    'amount' => $amount,
                    'item' => $invoice->item_tagihan ?: 'Tagihan Internet',
                    'nama_pelanggan' => $invoice->pelanggan?->nama_pelanggan,
                    'manual_invoice' => (bool) $invoice->manual_invoice,
                ];
            })
            ->filter(fn($item) => $item['amount'] > 0)
            ->values();
    }
}
