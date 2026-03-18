<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use Illuminate\Http\Request;

class IncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = IncidentReport::with('user')
            ->where('barangay_id', $request->user()->barangay_id)
            ->latest()
            ->get()
            ->map(fn($r) => $this->format($r));

        return response()->json(['reports' => $reports]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'category'    => 'required|in:flood,fire,accident,crime,infrastructure,health,other',
            'severity'    => 'required|in:low,moderate,high,critical',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'address'     => 'nullable|string',
        ]);

        $report = IncidentReport::create([
            'user_id'     => $request->user()->id,
            'barangay_id' => $request->user()->barangay_id,
            ...$request->only([
                'title', 'description', 'category',
                'severity', 'latitude', 'longitude', 'address',
            ]),
        ]);

        return response()->json([
            'message' => 'Incident reported successfully.',
            'report'  => $this->format($report),
        ], 201);
    }

    private function format(IncidentReport $r): array
    {
        return [
            'id'             => $r->id,
            'title'          => $r->title,
            'description'    => $r->description,
            'category'       => $r->category,
            'category_icon'  => $r->category_icon,
            'severity'       => $r->severity,
            'severity_color' => $r->severity_color,
            'latitude'       => $r->latitude,
            'longitude'      => $r->longitude,
            'address'        => $r->address,
            'status'         => $r->status,
            'reporter'       => $r->user?->name,
            'time_ago'       => $r->created_at->diffForHumans(),
            'created_at'     => $r->created_at->format('M d, Y h:i A'),
        ];
    }
}