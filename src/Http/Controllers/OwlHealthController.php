<?php

namespace Slowlyo\OwlHealth\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Slowlyo\OwlAdmin\Controllers\AdminController;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\ResultStores\CacheHealthResultStore;
use Spatie\Health\Health;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

class OwlHealthController extends AdminController
{
    public function index()
    {
        Artisan::call(RunHealthChecksCommand::class);

        $resultStore = new CacheHealthResultStore();

        $checkResults = $resultStore->latestResults();

        $json = $checkResults?->toJson();
        $json = json_decode($json, true);

        $page = $this->basePage()->data(['health' => array_values($json['checkResults'])])->body([
            amis()->Panel()->body(
                amis()->Property()
                    ->column(2)
                    ->labelStyle(["fontWeight" => "bold"])
                    ->items([
                        ['label' => '服务器操作系统', 'content' => PHP_OS],
                        ['label' => 'Web服务器环境', 'content' => $_SERVER['SERVER_SOFTWARE']],
                        ['label' => 'PHP 版本', 'content' => PHP_VERSION],
                        ['label' => 'Laravel 版本', 'content' => app()->version()],
                        ['label' => 'PHP 运行位数', 'content' => (PHP_INT_SIZE === 4 ? '32' : '64') . '位'],
                        ['label' => '文件上传最大值', 'content' => ini_get('upload_max_filesize')],
                        ['label' => 'POST 数据最大值', 'content' => ini_get('post_max_size')],
                        ['label' => '程序运行目录', 'content' => base_path()],
                    ])
            ),

            amis()->Cards()->source('${health}')->card(
                amis()->Card()
                    ->header([
                        'title'    => '${label}',
                        'subTitle' => '${shortSummary}',
                    ])
                    ->toolbar([
                        amis()->Tpl()
                            ->tpl('${status}')
                            ->className('label ${status == "ok" ? "label-success" : (status == "warning" ? "label-warning" : "label-danger")}'),
                    ])
                    ->body([
                        amis()->Each()->name('meta')->placeholder()->items([
                            amis()->Wrapper()
                                ->size('none')
                                ->visibleOn('${item.key}')
                                ->body('<span class="font-semibold">${item.key}:</span> ${item.value}'),
                            amis()->Wrapper()
                                ->size('none')
                                ->visibleOn('${ISTYPE(item, "string")}')
                                ->body('<span class="font-semibold">${item}</span>'),
                        ]),
                    ])
            ),
        ]);

        return $this->response()->success($page);
    }
}
