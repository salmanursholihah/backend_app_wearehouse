<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    /**
     * GET /api/user/activity-logs
     * User melihat activity log milik sendiri
     * (route ada di prefix user + role:user)
     */
    public function myLogs(Request $request)
    {
        $user = $request->user();

        $perPage = (int) $request->query('per_page', 10);

        $logs = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * (Opsional untuk admin app)
     * GET /api/admin/activity-logs
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);

        $logs = ActivityLog::with('user:id,name,email,role')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * (Opsional untuk admin app)
     * GET /api/admin/activity-logs/{id}
     */
    public function show($id)
    {
        $log = ActivityLog::with('user:id,name,email,role')->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Activity log tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

}
