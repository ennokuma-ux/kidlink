<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\EccdRecord;
use Illuminate\Http\Request;

class MilestonesController extends Controller
{
    private $domains = ['Gross Motor', 'Fine Motor', 'Self-Help', 'Receptive Language', 'Expressive Language', 'Cognitive'];

    public function index(Request $request)
    {
        $child = auth()->user()->children()->where('is_active', true)->first();

        if (!$child) {
            return redirect()->route('parent.dashboard')->with('info', 'No children registered.');
        }

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
            'child', 'records', 'domain', 'domainStats',
            'overallPercentage', 'achieved', 'inProgress', 'notYet'
        ) + ['domains' => $this->domains]);
    }
}
