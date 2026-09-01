<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Olt;
use App\Services\Olt\OltService;
use Illuminate\Support\Facades\DB;

class AdminOltController extends Controller
{
    public function index()
    {
        $olts = Olt::all();
        return view('admin.olt.index', compact('olts'));
    }

    public function create()
    {
        return view('admin.olt.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_olt' => 'required|string|max:100',
            'ip_address' => 'required|ip',
            'port' => 'required|integer',
            'protokol' => 'required|in:ssh,telnet',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:100',
            'snmp_community' => 'required|string|max:100',
            'tipe_olt' => 'required|in:zte,huawei,hsgq,vsol,cdata,global',
        ]);

        Olt::create($data);

        return redirect()->route('olt.index')->with('success', 'OLT berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $olt = Olt::findOrFail($id);
        return view('admin.olt.edit', compact('olt'));
    }

    public function update(Request $request, $id)
    {
        $olt = Olt::findOrFail($id);
        $data = $request->validate([
            'nama_olt' => 'required|string|max:100',
            'ip_address' => 'required|ip',
            'port' => 'required|integer',
            'protokol' => 'required|in:ssh,telnet',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:100',
            'snmp_community' => 'required|string|max:100',
            'tipe_olt' => 'required|in:zte,huawei,hsgq,vsol,cdata,global',
        ]);

        $olt->update($data);

        return redirect()->route('olt.index')->with('success', 'Konfigurasi OLT berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $olt = Olt::findOrFail($id);
        $olt->delete();
        return redirect()->route('olt.index')->with('success', 'OLT berhasil dihapus.');
    }

    public function autofind($id)
    {
        $olt = Olt::findOrFail($id);
        $onus = [];
        $error = null;

        try {
            $driver = OltService::getDriver($olt);
            if ($driver->connect()) {
                $onus = $driver->getUnregisteredOnus();
                $driver->disconnect();
            } else {
                $error = 'Gagal terhubung ke OLT. Periksa IP, Port, atau Kredensial SSH/Telnet.';
            }
        } catch (\Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        return view('admin.olt.autofind', compact('olt', 'onus', 'error'));
    }

    public function register(Request $request, $id)
    {
        $olt = Olt::findOrFail($id);
        $request->validate([
            'gpon_port' => 'required|string',
            'sn' => 'required|string',
            'model' => 'required|string',
            'onu_id' => 'required|integer',
        ]);

        try {
            $driver = OltService::getDriver($olt);
            if ($driver->connect()) {
                $success = $driver->registerOnu([
                    'gpon_port' => $request->gpon_port,
                    'sn' => $request->sn,
                    'model' => $request->model,
                    'onu_id' => $request->onu_id,
                ]);
                $driver->disconnect();

                if ($success) {
                    return redirect()->route('admin.olt.autofind', $olt->id_olt)->with('success', "ONU {$request->sn} berhasil didaftarkan di OLT.");
                }
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.olt.autofind', $olt->id_olt)->withErrors(['error' => 'Gagal mendaftarkan ONU: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.olt.autofind', $olt->id_olt)->withErrors(['error' => 'Gagal mendaftarkan ONU. Tidak dapat terhubung ke OLT.']);
    }

    public function monitoring($id)
    {
        $olt = Olt::findOrFail($id);
        
        $cpes = DB::table('tb_cpe')
            ->leftJoin('tb_pelanggan', 'tb_cpe.id_pelanggan', '=', 'tb_pelanggan.id_pelanggan')
            ->select('tb_cpe.*', 'tb_pelanggan.nama_pelanggan')
            ->get();

        $monitoredCpes = [];
        $error = null;

        try {
            $driver = OltService::getDriver($olt);
            if ($driver->connect()) {
                foreach ($cpes as $cpe) {
                    $power = $driver->getOnuOpticalPower($cpe->pppoe_conn_key ?: 'gpon-olt_1/1/1', '1', $cpe->serial_number);
                    
                    $monitoredCpes[] = [
                        'serial_number' => $cpe->serial_number,
                        'nama_pelanggan' => $cpe->nama_pelanggan ?: 'Belum Terhubung',
                        'ip_address' => $cpe->ip_address,
                        'rx_power_cpe' => $cpe->rx_power ?: 'N/A',
                        'rx_power_olt' => $power['rx_power'] ?: 'N/A',
                        'tx_power_olt' => $power['tx_power'] ?: 'N/A',
                        'last_inform' => $cpe->last_inform,
                    ];
                }
                $driver->disconnect();
            } else {
                $error = 'Gagal terhubung ke OLT. Periksa IP, Port, atau Kredensial.';
                foreach ($cpes as $cpe) {
                    $monitoredCpes[] = [
                        'serial_number' => $cpe->serial_number,
                        'nama_pelanggan' => $cpe->nama_pelanggan ?: 'Belum Terhubung',
                        'ip_address' => $cpe->ip_address,
                        'rx_power_cpe' => $cpe->rx_power ?: 'N/A',
                        'rx_power_olt' => 'N/A',
                        'tx_power_olt' => 'N/A',
                        'last_inform' => $cpe->last_inform,
                    ];
                }
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            foreach ($cpes as $cpe) {
                $monitoredCpes[] = [
                    'serial_number' => $cpe->serial_number,
                    'nama_pelanggan' => $cpe->nama_pelanggan ?: 'Belum Terhubung',
                    'ip_address' => $cpe->ip_address,
                    'rx_power_cpe' => $cpe->rx_power ?: 'N/A',
                    'rx_power_olt' => 'N/A',
                    'tx_power_olt' => 'N/A',
                    'last_inform' => $cpe->last_inform,
                ];
            }
        }

        return view('admin.olt.monitoring', compact('olt', 'monitoredCpes', 'error'));
    }
}
