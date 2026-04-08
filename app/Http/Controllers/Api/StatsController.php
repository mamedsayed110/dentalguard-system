<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    // لا تُستعمل مباشرة
    public function index()
    {
        return response()->json(['error' => 'Not allowed'], 403);
    }

    // داشبورد الدكتور
    public function doctorStats()
    {
        $doctorId = auth()->id();

        $stats = Cache::remember("doctor_stats_{$doctorId}", 60, function () use ($doctorId) {
            $scanQuery = Scan::where('user_id', $doctorId);

            return [
                'patients' => Patient::where('doctor_id', $doctorId)->count(),
                'total_scans' => $scanQuery->count(),
                'completed_scans' => (clone $scanQuery)->where('status','completed')->count(),
                'pending_scans' => (clone $scanQuery)->where('status','pending')->count(),
                'failed_scans' => (clone $scanQuery)->where('status','failed')->count(),
            ];
        });

        return response()->json($stats);
    }

    // داشبورد الأدمن
    public function adminStats()
    {
        $stats = Cache::remember("admin_stats", 60, function () {
            return [
                'total_users' => User::count(),
                'total_doctors' => User::where('role','doctor')->count(),
                'total_patients' => Patient::count(),
                'total_scans' => Scan::count(),
                'completed_scans' => Scan::where('status','completed')->count(),
                'pending_scans' => Scan::where('status','pending')->count(),
                'failed_scans' => Scan::where('status','failed')->count(),
            ];
        });

        return response()->json($stats);
    }

    // الرسوم البيانية اليومية
    public function scansByDate()
    {
        return Scan::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day','asc')
            ->get();
    }

    // توزيع الأمراض (من AI)
    public function diseasesDistribution()
    {
        $scans = Scan::where('status','completed')->get();

        $result = [];

        foreach ($scans as $scan) {
            foreach (($scan->result['detections'] ?? []) as $disease) {
                $result[$disease] = ($result[$disease] ?? 0) + 1;
            }
        }

        return $result;
    }
}
