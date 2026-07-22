<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use App\Models\CarePlan;
use App\Models\Feedback;
use App\Models\FileRecord;
use App\Models\WebAdmin;
use App\Services\Firebase\FirebaseAuthService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $firebaseReady = app(FirebaseAuthService::class)->configured();

        return [
            Stat::make('Admins', WebAdmin::query()->count())
                ->description('MySQL web admins')
                ->icon('heroicon-o-shield-check'),
            Stat::make('Files', FileRecord::query()->count())
                ->description('Private file metadata')
                ->icon('heroicon-o-folder-open'),
            Stat::make('Open feedback', Feedback::query()->where('status', 'open')->count())
                ->description('Needs review')
                ->icon('heroicon-o-chat-bubble-left-right'),
            Stat::make('Care plans', CarePlan::query()->where('status', 'published')->count())
                ->description('Published')
                ->icon('heroicon-o-document-text'),
            Stat::make('Audit logs', AuditLog::query()->count())
                ->description('Recorded actions')
                ->icon('heroicon-o-clipboard-document-list'),
            Stat::make('Firebase Admin', $firebaseReady ? 'Configured' : 'Not configured')
                ->description($firebaseReady ? 'Token verify ready' : 'Set service-account.json')
                ->color($firebaseReady ? 'success' : 'warning')
                ->icon('heroicon-o-fire'),
        ];
    }
}
