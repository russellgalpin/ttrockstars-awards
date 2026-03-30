<?php

namespace App\Http\Controllers;

use App\Models\GameAttempt;
use App\Models\GameAttemptAnswer;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $level = $request->query('award_level', 'gold');
        $user = $request->user();

        $recentAttempts = GameAttempt::query()
            ->where('user_id', $user->id)
            ->where('award_level', $level)
            ->latest()
            ->limit(20)
            ->get(['id', 'correct_answers', 'total_questions', 'incorrect_answers', 'time_remaining', 'created_at']);

        return Inertia::render('Stats', [
            'awardLevel' => $level,
            'recentAttempts' => $recentAttempts,
            'perTableAccuracy' => Inertia::defer(fn () => $this->perTableAccuracy($user, $level)),
            'perOperatorAccuracy' => Inertia::defer(fn () => $this->perOperatorAccuracy($user, $level)),
            'heatmapData' => Inertia::defer(fn () => $this->heatmapData($user, $level)),
        ]);
    }

    /**
     * @return array<int, array{table_number: int, total: int, correct: int, percentage: int}>
     */
    protected function perTableAccuracy(User $user, string $level): array
    {
        return GameAttemptAnswer::query()
            ->whereHas('gameAttempt', fn ($q) => $q->where('user_id', $user->id)->where('award_level', $level))
            ->selectRaw('table_number, COUNT(*) as total, SUM(is_correct) as correct')
            ->groupBy('table_number')
            ->orderBy('table_number')
            ->get()
            ->map(fn ($row) => [
                'table_number' => $row->table_number,
                'total' => (int) $row->total,
                'correct' => (int) $row->correct,
                'percentage' => $row->total > 0 ? (int) round(($row->correct / $row->total) * 100) : 0,
            ])
            ->all();
    }

    /**
     * @return array<int, array{operator: string, total: int, correct: int, percentage: int}>
     */
    protected function perOperatorAccuracy(User $user, string $level): array
    {
        return GameAttemptAnswer::query()
            ->whereHas('gameAttempt', fn ($q) => $q->where('user_id', $user->id)->where('award_level', $level))
            ->selectRaw('operator, COUNT(*) as total, SUM(is_correct) as correct')
            ->groupBy('operator')
            ->get()
            ->map(fn ($row) => [
                'operator' => $row->operator,
                'total' => (int) $row->total,
                'correct' => (int) $row->correct,
                'percentage' => $row->total > 0 ? (int) round(($row->correct / $row->total) * 100) : 0,
            ])
            ->all();
    }

    /**
     * @return array<int, array{table_number: int, multiplier: int, total: int, correct: int, percentage: int}>
     */
    protected function heatmapData(User $user, string $level): array
    {
        return GameAttemptAnswer::query()
            ->whereHas('gameAttempt', fn ($q) => $q->where('user_id', $user->id)->where('award_level', $level))
            ->selectRaw('table_number, multiplier, COUNT(*) as total, SUM(is_correct) as correct')
            ->groupBy('table_number', 'multiplier')
            ->get()
            ->map(fn ($row) => [
                'table_number' => $row->table_number,
                'multiplier' => $row->multiplier,
                'total' => (int) $row->total,
                'correct' => (int) $row->correct,
                'percentage' => $row->total > 0 ? (int) round(($row->correct / $row->total) * 100) : 0,
            ])
            ->all();
    }
}
