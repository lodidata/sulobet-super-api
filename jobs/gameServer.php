<?php
require __DIR__ . '/../repo/vendor/autoload.php';
$settings = require __DIR__ . '/../config/settings.php';

\Workerman\Worker::$logFile = LOG_PATH . '/php/gameServer.log';
$worker = new \Workerman\Worker();
$worker->count = 17;
$worker->name = 'lodiSuperGameServer';

// 防多开配置
// if ($app->getContainer()->redis->get(\Logic\Define\CacheKey::$perfix['callbackServer'])) {
//     echo 'callbackServer服务已启动，如果已关闭, 请等待5秒再启动', PHP_EOL;
//     exit;
// }

$worker->onWorkerStart = function ($worker) {
    global $app, $logger;
    /**********************config start*******************/
    $settings = require __DIR__ . '/../config/settings.php';
    $app = new \Slim\App($settings);
    require __DIR__ . '/src/dependencies.php';
    require __DIR__ . '/src/middleware.php';
    $app->run();
    $app->getContainer()->db->getConnection('default');
    $logger = $app->getContainer()->logger;

    /**********************config end*******************/

    $processId = 0;
    // 清理game_order_error表 1
    if ($worker->id === $processId) {
        $interval = 30;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameLogic($app->getContainer());
            $game->clearGameOrderError();
        });
    }

    $processId++;
    // rpt_orders_middle_day表 2
    if ($worker->id === $processId) {
        $interval = 600;//10分钟一次
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->rptSuperOrdersMiddleDay();
        });
    }

    $processId++;
    // rpt_orders_middle_day表 3
    // 第二天修正数据,4点执行
    if ($worker->id === $processId) {
        $interval = 600;//10分钟一次
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            if(date('H') == '04') {
                $game = new \Logic\Game\GameApi($app->getContainer());
                $game->rptSuperOrdersMiddleDayRevise(-1);
            }
        });
    }

    $processId++;
    //小飞机警告消息 4
    if ($worker->id === $processId && RUNMODE == 'product') {
        $interval = 120;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->gameOrderAWSAlarmMsg();
        });
    }

    $processId++;
    //小飞机game_order_error警告消息 5
    if ($worker->id === $processId && RUNMODE == 'product') {
        $interval = 120;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->gameOrderErrorAWSAlarmMsg();
        });
    }

    /********************************************************/

    $processId++;
    // 第三方  FC 拉订单 7
    if ($worker->id === $processId) {
        $interval = 600;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousCheckData('FC');
        });
    }

    $processId++;
    //FC 拉订单 8
    if ($worker->id === $processId) {
        $interval = 120;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousData('FC');
        });
    }

    $processId++;
    // 第三方  JILI 拉订单 9
    if ($worker->id === $processId) {
        $interval = 600;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousCheckData('JILI');
        });
    }

    $processId++;
    //JILI 拉订单 10
    if ($worker->id === $processId) {
        $interval = 120;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousData('JILI');
        });
    }

    $processId++;
    // PG 拉订单 11
    if ($worker->id === $processId) {
        $interval = 120;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousData('PG');
        });
    }

    $processId++;
    // 第三方  PG 拉订单 12
    if ($worker->id === $processId) {
        $interval = 600;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousCheckData('PG');
        });
    }

    $processId++;
    //EVO 拉订单 13
    if ($worker->id === $processId) {
        $interval = 120;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousData('EVO');
        });
    }

    $processId++;
    //EVORT 14
    if ($worker->id === $processId) {
        $interval = 30;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousData('EVORT');
        });
    }

    $processId++;
    //HB 拉订单 15
    if ($worker->id === $processId) {
        $interval = 30;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousData('HB');
        });
    }

    $processId++;
    //QT 拉订单 16
    if ($worker->id === $processId) {
        $interval = 120;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousData('QT');
        });
    }

    $processId++;
    //EVONE 17
    if ($worker->id === $processId) {
        $interval = 30;
        \Workerman\Lib\Timer::add($interval, function () use ($app) {
            $game = new \Logic\Game\GameApi($app->getContainer());
            $game->synchronousData('EVONE');
        });
    }

};

\Workerman\Worker::runAll();