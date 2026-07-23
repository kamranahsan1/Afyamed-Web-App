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
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $firebase = app(FirebaseAuthService::class);
        $firebaseReady = $firebase->configured();
        $projectId = $firebase->projectId() ?? '';

        return [
            Stat::make('Admins', (string) WebAdmin::query()->count())
                ->description('MySQL web admins')
                ->icon('heroicon-o-shield-check'),
            Stat::make('Files', (string) FileRecord::query()->count())
                ->description('Private file metadata')
                ->icon('heroicon-o-folder-open'),
            Stat::make('Open feedback', (string) Feedback::query()->where('status', 'open')->count())
                ->description('Needs review')
                ->icon('heroicon-o-chat-bubble-left-right'),
            Stat::make('Care plans', (string) CarePlan::query()->where('status', 'published')->count())
                ->description('Published')
                ->icon('heroicon-o-document-text'),
            Stat::make('Audit logs', (string) AuditLog::query()->count())
                ->description('Recorded actions')
                ->icon('heroicon-o-clipboard-document-list'),
            Stat::make('Firebase Admin', $firebaseReady ? 'Configured' : 'Not configured')
                ->description($firebaseReady
                    ? ($projectId !== '' ? "Project: {$projectId}" : 'Token verify ready')
                    : 'Place service-account.json in storage/app/firebase/')
                ->color($firebaseReady ? 'success' : 'warning')
                ->icon('heroicon-o-cloud'),
        ];
    }
}
