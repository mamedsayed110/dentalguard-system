<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
class AuditController extends Controller
{
     public function mine(){
        return response()->json(
            AuditLog::where('user_id', auth()->id())
                ->latest()->limit(200)->get()
        );
    }
    /**
 * عرض جميع audit logs (Admin فقط)
 */
public function all(Request $request)
{
    $query = AuditLog::with('user:id,name,email,role');

    // Filters
    if ($request->has('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    if ($request->has('action')) {
        $query->where('action', $request->action);
    }

    if ($request->has('from')) {
        $query->whereDate('created_at', '>=', $request->from);
    }

    if ($request->has('to')) {
        $query->whereDate('created_at', '<=', $request->to);
    }

    $logs = $query->latest()->paginate($request->per_page ?? 50);

    return response()->json($logs);
}
}
