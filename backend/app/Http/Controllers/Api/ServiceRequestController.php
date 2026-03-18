<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    // GET /api/requests — list user's requests
    public function index(Request $request)
    {
        $requests = ServiceRequest::with('barangay')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn($r) => $this->formatRequest($r));

        return response()->json(['requests' => $requests]);
    }

    // POST /api/requests — submit new request
    public function store(Request $request)
    {
        $request->validate([
            'type'    => 'required|in:clearance,residency,indigency,business_permit,good_moral,blotter',
            'purpose' => 'required|string|min:10|max:500',
        ]);

        $serviceRequest = ServiceRequest::create([
            'user_id'     => $request->user()->id,
            'barangay_id' => $request->user()->barangay_id,
            'type'        => $request->type,
            'purpose'     => $request->purpose,
            'status'      => 'pending',
        ]);

        return response()->json([
            'message' => 'Request submitted successfully.',
            'request' => $this->formatRequest($serviceRequest),
        ], 201);
    }

    // GET /api/requests/{id} — view single request
    public function show(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::with('barangay')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'request' => $this->formatRequest($serviceRequest),
        ]);
    }

    // Private formatter
    private function formatRequest(ServiceRequest $r): array
    {
        return [
            'id'               => $r->id,
            'type'             => $r->type,
            'type_label'       => $this->typeLabel($r->type),
            'status'           => $r->status,
            'status_color'     => $r->status_color,
            'purpose'          => $r->purpose,
            'reference_number' => $r->reference_number,
            'remarks'          => $r->remarks,
            'barangay'         => $r->barangay?->name,
            'created_at'       => $r->created_at->format('M d, Y'),
            'processed_at'     => $r->processed_at?->format('M d, Y'),
            'approved_at'      => $r->approved_at?->format('M d, Y'),
        ];
    }

    private function typeLabel(string $type): string
    {
        return match($type) {
            'clearance'       => 'Barangay Clearance',
            'residency'       => 'Certificate of Residency',
            'indigency'       => 'Certificate of Indigency',
            'business_permit' => 'Business Permit',
            'good_moral'      => 'Certificate of Good Moral',
            'blotter'         => 'Blotter Report',
            default           => ucfirst($type),
        };
    }
}