<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\EccdRecord;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MilestonesController extends Controller
{
    /** Domain => key used by the eccd language files. */
    private const DOMAIN_KEYS = [
        'Gross Motor' => 'gross_motor',
        'Fine Motor' => 'fine_motor',
        'Self-Help' => 'self_help',
        'Receptive Language' => 'receptive',
        'Expressive Language' => 'expressive',
        'Cognitive' => 'cognitive',
        'Social-Emotional' => 'social_emotional',
    ];

    /** Higher wins when the same indicator was recorded more than once. */
    private const STATUS_RANK = ['Not Yet' => 0, 'In Progress' => 1, 'Achieved' => 2];

    public function index(Request $request)
    {
        $children = auth()->user()->activeChildren();

        if ($children->isEmpty()) {
            return redirect()->route('parent.dashboard')->with('info', 'No children registered.');
        }

        $selectedChildId = $request->query('child_id');
        $child = $children->where('id', $selectedChildId)->first() ?? $children->first();

        $domains = AnalyticsService::ECCD_DOMAINS;
        $activeDomain = (string) $request->query('domain', 'all');
        if (!in_array($activeDomain, $domains, true)) {
            $activeDomain = 'all';
        }

        $domainOrder = array_flip($domains);
        $labels = $this->indicatorLabels();

        // Every record for the child. A checklist can exist more than once if
        // two teachers assessed the same child, so keep one row per indicator:
        // the furthest progress wins, and the newest row breaks a tie.
        $all = EccdRecord::where('student_id', $child->id)
            ->get()
            ->sort(fn ($a, $b) => (self::STATUS_RANK[$b->status] ?? 0) <=> (self::STATUS_RANK[$a->status] ?? 0)
                ?: $b->id <=> $a->id)
            ->unique(fn ($r) => $r->domain . '|' . $r->indicator)
            ->sortBy(fn ($r) => sprintf('%03d-%09d', $domainOrder[$r->domain] ?? 99, $r->id))
            ->values();

        // The stats always describe the whole checklist; only the list below
        // is narrowed by the domain filter.
        $domainStats = [];
        foreach ($domains as $d) {
            $rows = $all->where('domain', $d);
            $total = $rows->count();
            $done = $rows->where('status', 'Achieved')->count();
            $domainStats[$d] = [
                'label' => __('eccd.domain_' . (self::DOMAIN_KEYS[$d] ?? '')),
                'total' => $total,
                'achieved' => $done,
                'percentage' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            ];
        }

        $overallPercentage = $all->count() > 0
            ? (int) round(($all->where('status', 'Achieved')->count() / $all->count()) * 100)
            : 0;

        $counts = [
            'Achieved' => $all->where('status', 'Achieved')->count(),
            'In Progress' => $all->where('status', 'In Progress')->count(),
            'Not Yet' => $all->where('status', 'Not Yet')->count(),
        ];

        $records = ($activeDomain === 'all' ? $all : $all->where('domain', $activeDomain))
            ->map(function ($r) use ($labels) {
                $r->indicator_label = $labels[$r->domain . '|' . $r->indicator] ?? $r->indicator ?? $r->domain;

                return $r;
            })
            ->groupBy('domain');

        return view('parent.milestones', [
            'children' => $children,
            'child' => $child,
            'domains' => $domains,
            'activeDomain' => $activeDomain,
            'domainStats' => $domainStats,
            'overallPercentage' => $overallPercentage,
            'counts' => $counts,
            'totalIndicators' => $all->count(),
            'recordsByDomain' => $records,
        ]);
    }

    /**
     * Stored indicators are the English text. Map each one to its translation
     * so the parent reads the milestone in their own language.
     */
    private function indicatorLabels(): array
    {
        // Up to 700 translation lookups, and the result only changes when the
        // language files do, so keep it per language instead of rebuilding it
        // on every page view.
        return Cache::remember('parent.milestones.labels.'.app()->getLocale(), 600, function () {
            $labels = [];

            foreach (self::DOMAIN_KEYS as $domain => $key) {
                for ($i = 0; $i < 100; $i++) {
                    $langKey = "eccd.{$key}_{$i}";
                    $english = trans($langKey, [], 'en');

                    if ($english === $langKey) {
                        break;
                    }

                    $labels[$domain . '|' . $english] = __($langKey);
                }
            }

            return $labels;
        });
    }
}
