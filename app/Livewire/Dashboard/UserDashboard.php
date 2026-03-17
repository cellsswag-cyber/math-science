<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Livewire\Component;

class UserDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard.user-dashboard', [
            'dashboard' => app(DashboardService::class)->getDashboardData((int) auth()->id()),
        ])->layout('layouts.app', [
            'title' => 'Dashboard',
        ]);
    }
}
