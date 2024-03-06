<?php

namespace Slowlyo\OwlHealth;

use Spatie\Health\Facades\Health;
use Slowlyo\OwlAdmin\Renderers\TextControl;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Slowlyo\OwlAdmin\Extend\ServiceProvider;
use Spatie\Health\Checks\Checks\HorizonCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\MeiliSearchCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\DatabaseSizeCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\ResultStores\CacheHealthResultStore;
use Spatie\Health\Checks\Checks\RedisMemoryUsageCheck;
use Spatie\Health\Checks\Checks\DatabaseTableSizeCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;

class OwlHealthServiceProvider extends ServiceProvider
{
    protected $menu = [
        [
            'title' => '环境检测',
            'url'   => '/owl-health',
            'icon'  => 'mage:health-square',
        ],
    ];

    public function boot()
    {
        parent::boot();

        $config                  = require base_path('vendor/spatie/laravel-health/config/health.php');
        $config['result_stores'] = [
            CacheHealthResultStore::class => [
                'store' => 'file',
            ],
        ];

        config()->set('health', $config);

        Health::checks([
            CacheCheck::new(),
            OptimizedAppCheck::new(),
            DatabaseCheck::new(),
            CpuLoadCheck::new()
                ->failWhenLoadIsHigherInTheLast5Minutes(2.0)
                ->failWhenLoadIsHigherInTheLast15Minutes(1.5),
            DatabaseConnectionCountCheck::new()->failWhenMoreConnectionsThan(100),
            DatabaseSizeCheck::new()->failWhenSizeAboveGb(errorThresholdGb: 5.0),
            DatabaseTableSizeCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            HorizonCheck::new(),
            MeiliSearchCheck::new(),
            QueueCheck::new(),
            RedisCheck::new(),
            RedisMemoryUsageCheck::new()->failWhenAboveMb(1000),
            ScheduleCheck::new(),
            UsedDiskSpaceCheck::new(),
        ]);
    }

    public function settingForm()
    {
        return $this->baseSettingForm()->body([
            TextControl::make()->name('value')->label('Value')->required(true),
        ]);
    }
}
