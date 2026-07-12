<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPenggunaController extends Controller
{
    public function index()
    {
        // Tampilkan akun staff dan administrator
        $users = User::whereIn('level', ['admin', 'kasir', 'teknisi', 'sales', 'mitra', 'noc'])->orderBy('id', 'desc')->get();
        // Tampilkan akun pelanggan (eager load pelanggan dan paket detail)
        $customerUsers = User::with('pelanggan.paketDetail')
            ->whereNotIn('level', ['admin', 'kasir', 'teknisi', 'sales', 'mitra', 'noc'])
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.pengguna.index', compact('users', 'customerUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:tb_user,username',
            'nama_user' => 'required|string',
            'password' => 'required|string',
            'level' => 'required|string|in:admin,kasir,teknisi,sales,mitra,noc',
            'phone_number' => 'required|string',
        ]);

        $user = User::create([
            'username' => htmlspecialchars(strip_tags($request->username)),
            'nama_user' => htmlspecialchars(strip_tags($request->nama_user)),
            'password' => $request->password, // Automatically hashed via model cast
            'level' => $request->level,
            'foto' => 'admin.png',
            'id_pelanggan' => 0, // 0 menandakan staff bukan client
            'phone_number' => htmlspecialchars(strip_tags($request->phone_number)),
        ]);

        // If NOC, insert default menu permissions
        if ($request->level === 'noc') {
            $nocMenus = ['dashboard', 'monitoring', 'order_pemasangan', 'tr069', 'odp', 'odc', 'mapping', 'pelanggan'];
            foreach ($nocMenus as $menuKey) {
                DB::table('tb_user_menu_access')->insert([
                    'id_user' => $user->id,
                    'menu_key' => $menuKey,
                ]);
            }
        }

        return redirect()->route('admin.pengguna.index')->with('success', 'Akun staff berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'username' => 'required|string|unique:tb_user,username,' . $request->id,
            'nama_user' => 'required|string',
            'password' => 'nullable|string',
            'level' => 'required|string|in:admin,kasir,teknisi,sales,mitra,noc,user',
            'phone_number' => 'required_unless:level,user|nullable|string',
        ]);

        $user = User::findOrFail($request->id);
        
        $oldUsername = $user->username;
        $newUsername = htmlspecialchars(strip_tags($request->username));
        $newPassword = $request->password;
        $isPasswordChanged = $request->filled('password');

        if ($user->level === 'user' || $request->level === 'user') {
            // Update customer user
            $data = [
                'username' => $newUsername,
                'nama_user' => htmlspecialchars(strip_tags($request->nama_user)),
                'level' => 'user', // Force level to user for customer users
            ];
            if ($isPasswordChanged) {
                $data['password'] = $newPassword; // Store as plain text for customer users to bypass hash cast
            }
            
            DB::table('tb_user')->where('id', $user->id)->update($data);
            
            // Sync nama_user with tb_pelanggan.nama_pelanggan
            if ($user->id_pelanggan > 0) {
                DB::table('tb_pelanggan')->where('id_pelanggan', $user->id_pelanggan)->update([
                    'nama_pelanggan' => htmlspecialchars(strip_tags($request->nama_user)),
                ]);
                
                // Mikrotik Sync
                $checkUser = DB::table('tbl_penggunamikrotik')->first();
                if ($checkUser && $checkUser->addppsecret == 'ya') {
                    $pelanggan = DB::table('tb_pelanggan')->where('id_pelanggan', $user->id_pelanggan)->first();
                    if ($pelanggan && $pelanggan->id_mikrotik) {
                        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $pelanggan->id_mikrotik)->first();
                        if ($mikrotik) {
                            require_once base_path('include/routeros_api.php');
                            $API = new \RouterosAPI();
                            if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                                // Find secret by old username
                                $API->write('/ppp/secret/print', false);
                                $API->write('?name=' . $oldUsername);
                                $secrets = $API->read();
                                
                                if (!empty($secrets)) {
                                    $secretId = $secrets[0]['.id'];
                                    
                                    $params = [
                                        ".id" => $secretId,
                                        "name" => $newUsername,
                                    ];
                                    if ($isPasswordChanged) {
                                        $params["password"] = $newPassword;
                                    }
                                    
                                    $API->comm("/ppp/secret/set", $params);
                                }
                                $API->disconnect();
                            }
                        }
                    }
                }
            }
            
            return redirect()->route('admin.pengguna.index')->with('success', 'Akun pelanggan berhasil diubah!');
        } else {
            // Update staff user
            $data = [
                'username' => $newUsername,
                'nama_user' => htmlspecialchars(strip_tags($request->nama_user)),
                'level' => $request->level,
                'phone_number' => htmlspecialchars(strip_tags($request->phone_number)),
            ];

            if ($isPasswordChanged) {
                $data['password'] = $newPassword; // Automatically hashed via model cast
            }

            $user->update($data);

            return redirect()->route('admin.pengguna.index')->with('success', 'Akun staff berhasil diubah!');
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $user = User::findOrFail($request->id);
        
        // Mencegah penghapusan akun diri sendiri yang sedang login
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.']);
        }

        if ($user->id_pelanggan > 0) {
            $id = $user->id_pelanggan;
            $pelanggan = DB::table('tb_pelanggan')->where('id_pelanggan', $id)->first();

            // Delete from Mikrotik if enabled
            $checkUser = DB::table('tbl_penggunamikrotik')->first();
            if ($checkUser && $checkUser->addppsecret == 'ya' && $pelanggan) {
                $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $pelanggan->id_mikrotik)->first();
                if ($mikrotik) {
                    require_once base_path('include/routeros_api.php');
                    $API = new \RouterosAPI();
                    if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                        // Find secret
                        $API->write('/ppp/secret/print', false);
                        $API->write('?name=' . $user->username);
                        $secrets = $API->read();
                        if (!empty($secrets)) {
                            $API->write('/ppp/secret/remove', false);
                            $API->write('=.id=' . $secrets[0]['.id']);
                            $API->read();
                        }
                        $API->disconnect();
                    }
                }
            }

            // Delete from DB tables
            DB::table('tb_user')->where('id_pelanggan', $id)->delete();
            DB::table('tb_pelanggan')->where('id_pelanggan', $id)->delete();

            return redirect()->route('admin.pengguna.index')->with('success', 'Akun pelanggan beserta data profil langganan berhasil dihapus!');
        } else {
            $user->delete();
            return redirect()->route('admin.pengguna.index')->with('success', 'Akun staff berhasil dihapus!');
        }
    }

    public function gantiPasswordSelf(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal harus 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();

        // Cek apakah password lama cocok
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        // Simpan password baru
        $user->update([
            'password' => $request->new_password,
        ]);

        return back()->with('success', 'Password Anda berhasil diubah!');
    }
}
