@extends('layouts.admin')

@section('title', 'Log Aktivitas Sistem')

@section('styles')
<style>
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.25);
    }

    .btn-secondary {
        background-color: #e2e8f0;
        color: #334155;
    }
    .btn-secondary:hover {
        background-color: #cbd5e1;
    }

    .table-container {
        margin-top: 20px;
        overflow-x: auto;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .table th, .table td {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .table th {
        font-weight: 600;
        color: var(--text-gray);
        background-color: #f8fafc;
    }

    .table tr {
        transition: background-color 0.2s;
    }

    .table tr.log-row:hover {
        background-color: #f8fafc;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-info {
        background-color: #eff6ff;
        color: #2563eb;
    }

    .badge-error {
        background-color: #fef2f2;
        color: #dc2626;
    }

    .badge-warning {
        background-color: #fff7ed;
        color: #ea580c;
    }

    .badge-debug {
        background-color: #f1f5f9;
        color: #475569;
    }

    .badge-critical {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fee2e2;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.95rem;
        outline: none;
        width: 100%;
        transition: border 0.2s;
    }

    .form-control:focus {
        border-color: #4f46e5;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
        <div class="card-title">
            <i class="fa-solid fa-file-shield" style="font-size: 1.5rem; color: #4f46e5;"></i>
            <span style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 700; color: #0f172a;">Log Aktivitas Sistem</span>
        </div>
    </div>

    <!-- Filter Form -->
    <div style="margin-top: 20px; background: #f8fafc; padding: 18px; border-radius: 16px; border: 1px solid #e2e8f0;">
        <form method="GET" action="{{ route('admin.logs.index') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Cari Pesan Log</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Kata kunci pesan..." value="{{ $search }}" style="padding-left: 38px; height: 42px;">
                </div>
            </div>

            <div style="width: 180px; min-width: 150px;">
                <label style="font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Filter Level</label>
                <select name="level" class="form-control" style="height: 42px;">
                    <option value="">-- Semua Level --</option>
                    <option value="info" {{ strtolower($level) == 'info' ? 'selected' : '' }}>INFO</option>
                    <option value="error" {{ strtolower($level) == 'error' ? 'selected' : '' }}>ERROR</option>
                    <option value="warning" {{ strtolower($level) == 'warning' ? 'selected' : '' }}>WARNING</option>
                    <option value="debug" {{ strtolower($level) == 'debug' ? 'selected' : '' }}>DEBUG</option>
                    <option value="critical" {{ strtolower($level) == 'critical' ? 'selected' : '' }}>CRITICAL</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 42px;">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if($search || $level)
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary" style="height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                        Clear
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Logs Table -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th style="width: 180px;">Waktu</th>
                    <th style="width: 120px;">Level</th>
                    <th>Pesan</th>
                    <th style="width: 100px; text-align: center;">Trace</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginatedLogs as $index => $log)
                    @php
                        $displayIndex = $paginatedLogs->firstItem() + $index;
                        $levelLower = strtolower($log['level']);
                        $badgeClass = 'badge-info';
                        if (in_array($levelLower, ['error', 'fail', 'failure'])) {
                            $badgeClass = 'badge-error';
                        } elseif (in_array($levelLower, ['warning', 'notice'])) {
                            $badgeClass = 'badge-warning';
                        } elseif ($levelLower === 'debug') {
                            $badgeClass = 'badge-debug';
                        } elseif (in_array($levelLower, ['critical', 'alert', 'emergency'])) {
                            $badgeClass = 'badge-critical';
                        }
                    @endphp
                    <tr class="log-row">
                        <td>{{ $displayIndex }}</td>
                        <td style="white-space: nowrap; color: #475569; font-weight: 500;">
                            {{ $log['timestamp'] }}
                        </td>
                        <td>
                            <span class="badge {{ $badgeClass }}">{{ $log['level'] }}</span>
                        </td>
                        <td style="font-family: monospace; font-size: 0.85rem; color: #0f172a; word-break: break-all;">
                            {{ $log['message'] }}
                        </td>
                        <td align="center">
                            @if(!empty($log['details']))
                                <button type="button" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.78rem; border-radius: 8px; gap: 4px;" onclick="toggleDetails('details-{{ $index }}')">
                                    <i class="fa-solid fa-terminal"></i> Detail
                                </button>
                            @else
                                <span style="color: #cbd5e1; font-size: 0.8rem;">-</span>
                            @endif
                        </td>
                    </tr>
                    @if(!empty($log['details']))
                        <tr id="details-{{ $index }}" style="display: none; background: #0f172a;">
                            <td colspan="5" style="padding: 16px 24px; border-bottom: 1px solid #1e293b;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <span style="color: #94a3b8; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <i class="fa-solid fa-code"></i> Stack Trace / Detail Log
                                    </span>
                                    <button type="button" class="btn btn-secondary" style="padding: 4px 8px; font-size: 0.72rem; border-radius: 6px; background: #1e293b; color: #cbd5e1; border: none;" onclick="copyTrace('trace-content-{{ $index }}')">
                                        <i class="fa-regular fa-copy"></i> Salin
                                    </button>
                                </div>
                                <pre id="trace-content-{{ $index }}" style="margin: 0; font-family: 'Fira Code', 'Consolas', monospace; font-size: 0.8rem; color: #38bdf8; overflow-x: auto; white-space: pre-wrap; word-break: break-all; max-height: 400px; background: #020617; padding: 14px; border-radius: 10px; border: 1px solid #1e293b; text-align: left;">{{ implode("\n", $log['details']) }}</pre>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" align="center" style="color: var(--text-gray); padding: 30px;">
                            Tidak ada log aktivitas sistem yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Custom Pagination Component -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 0.85rem; color: var(--text-gray); font-weight: 500;">
            Menampilkan {{ $paginatedLogs->firstItem() ?? 0 }} - {{ $paginatedLogs->lastItem() ?? 0 }} dari {{ $paginatedLogs->total() }} Log
        </div>
        
        @if($paginatedLogs->lastPage() > 1)
            <div style="display: flex; align-items: center; gap: 6px;">
                {{-- Previous Button --}}
                @if($paginatedLogs->onFirstPage())
                    <span style="padding: 8px 12px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; font-size: 0.85rem; cursor: not-allowed; font-weight: 600;">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $paginatedLogs->previousPageUrl() }}" style="padding: 8px 12px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $start = max(1, $paginatedLogs->currentPage() - 2);
                    $end = min($paginatedLogs->lastPage(), $paginatedLogs->currentPage() + 2);
                @endphp
                
                @if($start > 1)
                    <a href="{{ $paginatedLogs->url(1) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">1</a>
                    @if($start > 2)
                        <span style="color: var(--text-gray); padding: 0 4px;">...</span>
                    @endif
                @endif

                @for($p = $start; $p <= $end; $p++)
                    @if($p == $paginatedLogs->currentPage())
                        <span style="padding: 8px 14px; border-radius: 8px; background: var(--primary-gradient); color: white; font-size: 0.85rem; font-weight: 700; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);">{{ $p }}</span>
                    @else
                        <a href="{{ $paginatedLogs->url($p) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">{{ $p }}</a>
                    @endif
                @endfor

                @if($end < $paginatedLogs->lastPage())
                    @if($end < $paginatedLogs->lastPage() - 1)
                        <span style="color: var(--text-gray); padding: 0 4px;">...</span>
                    @endif
                    <a href="{{ $paginatedLogs->url($paginatedLogs->lastPage()) }}" style="padding: 8px 14px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">{{ $paginatedLogs->lastPage() }}</a>
                @endif

                {{-- Next Button --}}
                @if($paginatedLogs->hasMorePages())
                    <a href="{{ $paginatedLogs->nextPageUrl() }}" style="padding: 8px 12px; border-radius: 8px; background: #e2e8f0; color: #334155; font-size: 0.85rem; text-decoration: none; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @else
                    <span style="padding: 8px 12px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; font-size: 0.85rem; cursor: not-allowed; font-weight: 600;">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    function toggleDetails(rowId) {
        const row = document.getElementById(rowId);
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    }

    function copyTrace(elementId) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(function() {
            alert('Trace berhasil disalin ke clipboard!');
        }, function(err) {
            alert('Gagal menyalin trace: ', err);
        });
    }
</script>
@endsection
