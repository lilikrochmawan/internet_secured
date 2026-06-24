<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SubBranch;
use App\Models\User;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPengaturanClientController extends Controller
{
    public function index()
    {
        // Fetch all branches with sub-branches
        $branches = Branch::with('subBranches')->orderBy('id', 'desc')->get();
        
        // Fetch all staff users (excluding normal customers)
        $users = User::whereIn('level', ['admin', 'kasir', 'teknisi', 'sales', 'mitra'])
            ->where('id_pelanggan', 0)
            ->orderBy('id', 'desc')
            ->get();
            
        // Map staff users with their branch and menu access
        foreach ($users as $user) {
            $user->access_list = DB::table('tb_user_branch_access')->where('id_user', $user->id)->get();
            $user->menu_access_list = $user->getMenuAccess();
        }

        return view('admin.pengaturan_client.index', compact('branches', 'users'));
    }

    // --- Branch Methods ---
    public function storeBranch(Request $request)
    {
        $request->validate([
            'nama_branch' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        Branch::create([
            'nama_branch' => htmlspecialchars(strip_tags($request->nama_branch)),
            'deskripsi' => htmlspecialchars(strip_tags($request->deskripsi)),
        ]);

        return redirect()->route('admin.pengaturan_client.index')->with('success', 'Branch baru berhasil ditambahkan!');
    }

    public function updateBranch(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'nama_branch' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $branch = Branch::findOrFail($request->id);
        $branch->update([
            'nama_branch' => htmlspecialchars(strip_tags($request->nama_branch)),
            'deskripsi' => htmlspecialchars(strip_tags($request->deskripsi)),
        ]);

        return redirect()->route('admin.pengaturan_client.index')->with('success', 'Detail Branch berhasil diubah!');
    }

    public function destroyBranch(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $branch = Branch::findOrFail($request->id);
        
        // Check if there are pelanggan belonging to this branch
        $pelangganCount = Pelanggan::where('id_branch', $branch->id)->count();
        if ($pelangganCount > 0) {
            return back()->withErrors(['error' => 'Branch ini tidak dapat dihapus karena memiliki pelanggan aktif. Silakan pindahkan pelanggan terlebih dahulu.']);
        }

        $branch->delete();
        return redirect()->route('admin.pengaturan_client.index')->with('success', 'Branch berhasil dihapus!');
    }

    // --- Sub-Branch Methods ---
    public function storeSubBranch(Request $request)
    {
        $request->validate([
            'id_branch' => 'required|integer',
            'nama_sub_branch' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        SubBranch::create([
            'id_branch' => $request->id_branch,
            'nama_sub_branch' => htmlspecialchars(strip_tags($request->nama_sub_branch)),
            'deskripsi' => htmlspecialchars(strip_tags($request->deskripsi)),
        ]);

        return redirect()->route('admin.pengaturan_client.index')->with('success', 'Sub-Branch baru berhasil ditambahkan!');
    }

    public function updateSubBranch(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'nama_sub_branch' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $subBranch = SubBranch::findOrFail($request->id);
        $subBranch->update([
            'nama_sub_branch' => htmlspecialchars(strip_tags($request->nama_sub_branch)),
            'deskripsi' => htmlspecialchars(strip_tags($request->deskripsi)),
        ]);

        return redirect()->route('admin.pengaturan_client.index')->with('success', 'Detail Sub-Branch berhasil diubah!');
    }

    public function destroySubBranch(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $subBranch = SubBranch::findOrFail($request->id);

        // Check if there are pelanggan belonging to this sub branch
        $pelangganCount = Pelanggan::where('id_sub_branch', $subBranch->id)->count();
        if ($pelangganCount > 0) {
            return back()->withErrors(['error' => 'Sub-Branch ini tidak dapat dihapus karena memiliki pelanggan aktif. Silakan pindahkan pelanggan terlebih dahulu.']);
        }

        $subBranch->delete();
        return redirect()->route('admin.pengaturan_client.index')->with('success', 'Sub-Branch berhasil dihapus!');
    }

    // --- Staff Access Management ---
    public function updateAccess(Request $request)
    {
        $request->validate([
            'id_user' => 'required|integer',
            'level' => 'required|string|in:admin,kasir,teknisi,sales,mitra',
            'branches' => 'nullable|array',
            'sub_branches' => 'nullable|array',
            'menus' => 'nullable|array',
        ]);

        $userId = $request->id_user;
        $user = User::findOrFail($userId);

        // Update staff user level/role
        $user->update([
            'level' => $request->level,
        ]);

        // Clear existing access entries
        DB::table('tb_user_branch_access')->where('id_user', $userId)->delete();

        // Save checked branches as a whole
        $branches = $request->input('branches', []);
        foreach ($branches as $branchId) {
            DB::table('tb_user_branch_access')->insert([
                'id_user' => $userId,
                'id_branch' => $branchId,
                'id_sub_branch' => null,
            ]);
        }

        // Save checked sub-branches (only if parent branch is not fully checked)
        $subBranches = $request->input('sub_branches', []);
        foreach ($subBranches as $subBranchId) {
            $subBranch = SubBranch::find($subBranchId);
            if ($subBranch) {
                if (in_array($subBranch->id_branch, $branches)) {
                    continue; // Already has full branch access
                }
                DB::table('tb_user_branch_access')->insert([
                    'id_user' => $userId,
                    'id_branch' => $subBranch->id_branch,
                    'id_sub_branch' => $subBranchId,
                ]);
            }
        }

        // Clear existing menu access entries
        DB::table('tb_user_menu_access')->where('id_user', $userId)->delete();

        // Save checked menu access list
        $menus = $request->input('menus', []);
        if (!in_array('dashboard', $menus)) {
            $menus[] = 'dashboard';
        }
        foreach ($menus as $menuKey) {
            DB::table('tb_user_menu_access')->insert([
                'id_user' => $userId,
                'menu_key' => $menuKey,
            ]);
        }

        return redirect()->route('admin.pengaturan_client.index')->with('success', 'Hak akses, role, dan menu staff berhasil diperbarui!');
    }
}
