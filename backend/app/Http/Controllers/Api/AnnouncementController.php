<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with('author')
            ->where('barangay_id', $request->user()->barangay_id)
            ->published()
            ->latest('is_pinned')
            ->latest();

        if ($request->category && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $announcements = $query->get()->map(fn($a) => $this->format($a));

        return response()->json(['announcements' => $announcements]);
    }

    public function show(Request $request, $id)
    {
        $announcement = Announcement::with('author')
            ->where('barangay_id', $request->user()->barangay_id)
            ->published()
            ->findOrFail($id);

        return response()->json(['announcement' => $this->format($announcement)]);
    }

    private function format(Announcement $a): array
    {
        return [
            'id'             => $a->id,
            'title'          => $a->title,
            'content'        => $a->content,
            'category'       => $a->category,
            'category_icon'  => $a->category_icon,
            'priority'       => $a->priority,
            'priority_color' => $a->priority_color,
            'is_pinned'      => $a->is_pinned,
            'author'         => $a->author?->name,
            'date'           => $a->created_at->format('M d, Y'),
            'time_ago'       => $a->created_at->diffForHumans(),
        ];
    }
}