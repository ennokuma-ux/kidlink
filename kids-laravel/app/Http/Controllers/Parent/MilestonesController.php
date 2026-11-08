<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\EccdRecord;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class MilestonesController extends Controller
{
    private $domains = AnalyticsService::ECCD_DOMAINS;

    public function index(Request $request)
    {
        $children = auth()->user()->children()->where('is_active', true)->get();

        if ($children->isEmpty()) {
            return redirect()->route('parent.dashboard')->with('info', 'No children registered.');
        }

        $selectedChildId = $request->query('child_id');
        $child = $children->where('id', $selectedChildId)->first() ?? $children->first();

        $domain = $request->domain ?? 'all';

        $records = EccdRecord::where('student_id', $child->id)
            ->when($domain !== 'all', fn($q) => $q->where('domain', $domain))
            ->orderBy('domain')
            ->get();

        $domainStats = [];
        foreach ($this->domains as $d) {
            $domainRecords = $records->where('domain', $d);
            $total = $domainRecords->count();
            $achieved = $domainRecords->where('status', 'Achieved')->count();
            $domainStats[$d] = [
                'total' => $total,
                'achieved' => $achieved,
                'percentage' => $total > 0 ? round(($achieved / $total) * 100) : 0,
            ];
        }

        $overallPercentage = $records->count() > 0
            ? round(($records->where('status', 'Achieved')->count() / max($records->count(), 1)) * 100)
            : 0;

        $achieved = $records->where('status', 'Achieved');
        $inProgress = $records->where('status', 'In Progress');
        $notYet = $records->where('status', 'Not Yet');

        return view('parent.milestones', compact(
            'children', 'child', 'records', 'domain', 'domainStats',
            'overallPercentage', 'achieved', 'inProgress', 'notYet'
        ) + ['domains' => $this->domains]);
    }
}
