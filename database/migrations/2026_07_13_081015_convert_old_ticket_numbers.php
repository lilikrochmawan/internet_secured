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
        $tickets = Illuminate\Support\Facades\DB::table('tbl_keluhan')->get();
        foreach ($tickets as $ticket) {
            $oldNo = $ticket->nomor_tiket;
            if (str_starts_with($oldNo, 'pengaduan-')) {
                $lastDashPos = strrpos($oldNo, '-');
                $numPart = substr($oldNo, $lastDashPos + 1);
                
                if (!empty($ticket->id_pelanggan)) {
                    $newNo = 'TKT,' . $ticket->id_pelanggan . ',' . $numPart;
                } else {
                    $newNo = 'INT,' . $numPart;
                }
                
                Illuminate\Support\Facades\DB::table('tbl_keluhan')
                    ->where('id_keluhan', $ticket->id_keluhan)
                    ->update(['nomor_tiket' => $newNo]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tickets = Illuminate\Support\Facades\DB::table('tbl_keluhan')->get();
        foreach ($tickets as $ticket) {
            $no = $ticket->nomor_tiket;
            if (str_starts_with($no, 'TKT,') || str_starts_with($no, 'INT,')) {
                $parts = explode(',', $no);
                $numPart = end($parts);
                $oldNo = 'pengaduan-' . $numPart;
                
                Illuminate\Support\Facades\DB::table('tbl_keluhan')
                    ->where('id_keluhan', $ticket->id_keluhan)
                    ->update(['nomor_tiket' => $oldNo]);
            }
        }
    }
};
