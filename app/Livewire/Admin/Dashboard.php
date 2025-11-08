<?php

namespace App\Livewire\Admin;

use App\Models\Link;
use App\Models\Note;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Get dashboard statistics with caching.
     */
    public function getStatsProperty(): array
    {
        return Cache::remember('admin.dashboard.stats', 60, function () {
            return [
                'total_links' => Link::count(),
                'total_notes' => Note::count(),
                'total_users' => User::count(),
                'pending_reports' => Report::where('status', 'pending')->count(),
                'active_links' => Link::where('is_active', true)->count(),
                'active_notes' => Note::where('is_active', true)->count(),
                'verified_users' => User::where('is_verified', true)->count(),
                'banned_users' => User::whereNotNull('banned_at')->count(),
            ];
        });
    }

    /**
     * Get system health metrics.
     */
    public function getHealthProperty(): array
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');

        return [
            'disk_usage_percent' => $totalSpace > 0 ? round((($totalSpace - $freeSpace) / $totalSpace) * 100, 1) : 0,
            'disk_free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
            'cache_working' => Cache::has('admin.dashboard.stats'),
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->layout('components.layouts.admin');
    }
}
