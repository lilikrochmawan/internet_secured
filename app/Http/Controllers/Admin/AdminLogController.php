<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $level = $request->get('level');
        $month = $request->get('month');
        $year = $request->get('year');
        $page = $request->get('page', 1);
        $perPage = 10;

        $allLogs = $this->parseLogs();

        // Apply filters if present
        if (!empty($level)) {
            $allLogs = array_values(array_filter($allLogs, function ($log) use ($level) {
                return strtolower($log['level']) === strtolower($level);
            }));
        }

        if (!empty($month)) {
            $allLogs = array_values(array_filter($allLogs, function ($log) use ($month) {
                // Extract month from timestamp YYYY-MM-DD HH:MM:SS
                $logMonth = substr($log['timestamp'], 5, 2);
                return $logMonth === $month;
            }));
        }

        if (!empty($year)) {
            $allLogs = array_values(array_filter($allLogs, function ($log) use ($year) {
                // Extract year from timestamp YYYY-MM-DD HH:MM:SS
                $logYear = substr($log['timestamp'], 0, 4);
                return $logYear === $year;
            }));
        }

        if (!empty($search)) {
            $searchLower = strtolower($search);
            $allLogs = array_values(array_filter($allLogs, function ($log) use ($searchLower) {
                return str_contains(strtolower($log['message']), $searchLower) || 
                       str_contains(strtolower($log['timestamp']), $searchLower) ||
                       str_contains(strtolower(implode("\n", $log['details'])), $searchLower);
            }));
        }

        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = array_slice($allLogs, $offset, $perPage);

        $paginatedLogs = new LengthAwarePaginator(
            $itemsForCurrentPage,
            count($allLogs),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.logs.index', compact('paginatedLogs', 'search', 'level', 'month', 'year'));
    }

    private function parseLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath)) {
            return [];
        }

        try {
            $file = new \SplFileObject($logPath, 'r');
            $lines = [];
            while (!$file->eof()) {
                $line = $file->fgets();
                if ($line !== false) {
                    $lines[] = $line;
                }
            }
            
            $entries = [];
            $currentEntry = null;
            
            foreach ($lines as $line) {
                $line = rtrim($line);
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([a-zA-Z0-9_-]+)\.([A-Z]+): (.*)$/', $line, $matches)) {
                    if ($currentEntry) {
                        $entries[] = $currentEntry;
                    }
                    $currentEntry = [
                        'timestamp' => $matches[1],
                        'environment' => $matches[2],
                        'level' => $matches[3],
                        'message' => $matches[4],
                        'details' => []
                    ];
                } else {
                    if ($currentEntry && trim($line) !== '') {
                        $currentEntry['details'][] = $line;
                    }
                }
            }
            if ($currentEntry) {
                $entries[] = $currentEntry;
            }
            
            // Reverse so that the latest logs appear at the top
            return array_reverse($entries);
        } catch (\Exception $e) {
            return [];
        }
    }
}
