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
            $no = $ticket->nomor_tiket;
            if (str_contains($no, ',')) {
                $newNo = str_replace(',', '', $no);
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
            if (str_starts_with($no, 'TKT') && !str_contains($no, ',')) {
                $idAndUrut = substr($no, 3);
                $urut = substr($idAndUrut, -3);
                $id = substr($idAndUrut, 0, strlen($idAndUrut) - 3);
                $oldNo = 'TKT,' . $id . ',' . $urut;
                
                Illuminate\Support\Facades\DB::table('tbl_keluhan')
                    ->where('id_keluhan', $ticket->id_keluhan)
                    ->update(['nomor_tiket' => $oldNo]);
            } elseif (str_starts_with($no, 'INT') && !str_contains($no, ',')) {
                $urut = substr($no, 3);
                $oldNo = 'INT,' . $urut;
                
                Illuminate\Support\Facades\DB::table('tbl_keluhan')
                    ->where('id_keluhan', $ticket->id_keluhan)
                    ->update(['nomor_tiket' => $oldNo]);
            }
        }
    }
};
