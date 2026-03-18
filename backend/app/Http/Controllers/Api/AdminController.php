<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── Dashboard stats ──────────────────────────────────
    public function stats(Request $request)
    {
        $barangayId = $request->user()->barangay_id;

        return response()->json([
            'stats' => [
                'total_residents'  => User::where('barangay_id', $barangayId)
                                         ->where('role', 'resident')->count(),
                'total_requests'   => ServiceRequest::where('barangay_id', $barangayId)->count(),
                'pending_requests' => ServiceRequest::where('barangay_id', $barangayId)
                                         ->where('status', 'pending')->count(),
                'announcements'    => Announcement::where('barangay_id', $barangayId)->count(),
            ],
        ]);
    }

    // ── List all requests (admin view) ───────────────────
    public function requests(Request $request)
    {
        $query = ServiceRequest::with(['user', 'barangay'])
            ->where('barangay_id', $request->user()->barangay_id)
            ->latest();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->get()->map(fn($r) => [
            'id'               => $r->id,
            'reference_number' => $r->reference_number,
            'type_label'       => $this->typeLabel($r->type),
            'type'             => $r->type,
            'status'           => $r->status,
            'status_color'     => $r->status_color,
            'purpose'          => $r->purpose,
            'remarks'          => $r->remarks,
            'resident_name'    => $r->user?->name,
            'resident_email'   => $r->user?->email,
            'created_at'       => $r->created_at->format('M d, Y'),
        ]);

        return response()->json(['requests' => $requests]);
    }

    // ── Update request status ────────────────────────────
    public function updateRequest(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|in:pending,processing,approved,rejected,released',
            'remarks' => 'nullable|string|max:500',
        ]);

        $serviceRequest = ServiceRequest::where('barangay_id', $request->user()->barangay_id)
            ->findOrFail($id);

        $serviceRequest->update([
            'status'       => $request->status,
            'remarks'      => $request->remarks,
            'processed_at' => in_array($request->status, ['processing']) ? now() : $serviceRequest->processed_at,
            'approved_at'  => in_array($request->status, ['approved', 'released']) ? now() : $serviceRequest->approved_at,
            'released_at'  => $request->status === 'released' ? now() : $serviceRequest->released_at,
        ]);

        return response()->json([
            'message' => 'Request status updated.',
            'request' => $serviceRequest->fresh(),
        ]);
    }

    // ── List all residents ───────────────────────────────
    public function residents(Request $request)
    {
        $residents = User::where('barangay_id', $request->user()->barangay_id)
            ->where('role', 'resident')
            ->latest()
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role,
                'created_at' => $u->created_at->format('M d, Y'),
            ]);

        return response()->json(['residents' => $residents]);
    }

    // ── Create announcement ──────────────────────────────
    public function createAnnouncement(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'category' => 'required|in:general,emergency,health,infrastructure,event,livelihood',
            'priority' => 'required|in:normal,important,urgent',
        ]);

        $announcement = Announcement::create([
            'barangay_id'  => $request->user()->barangay_id,
            'user_id'      => $request->user()->id,
            'title'        => $request->title,
            'content'      => $request->content,
            'category'     => $request->category,
            'priority'     => $request->priority,
            'is_pinned'    => $request->is_pinned ?? false,
            'is_published' => true,
            'published_at' => now(),
        ]);

        return response()->json([
            'message'      => 'Announcement posted.',
            'announcement' => $announcement,
        ], 201);
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