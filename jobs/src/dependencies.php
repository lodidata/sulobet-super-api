<?php
use Slim\Http\Request;
use Slim\Http\Response;
use Utils\www\Controller;
// DIC configuration
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$container = $app->getContainer();

// view renderer
// $container['renderer'] = function ($c) {
//     $settings = $c->get('settings')['renderer'];
//     return new Slim\Views\PhpRenderer($settings['template_path']);
// };

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());

    if (isset($settings['type']) && $settings['type'] == 'file') {
//        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        $logger->pushHandler(new Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']));//每天生成一个日志
    }

//    if (RUNMODE == 'dev') {
//        $firephp = new Monolog\Handler\FirePHPHandler();
//        $logger->pushHandler($firephp);
//    }

    return $logger;
};

$container['db'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    foreach ($c['settings']['db'] as $key => $v) {
        $capsule->addConnection($v, $key);
    }
    $capsule->setEventDispatcher(new Dispatcher(new Container));
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['redis'] = function ($c) {
    $settings   = $c->get('settings')['cache'];
    if(isset($settings['sentinels']) && is_array($settings['sentinels'])){
        return new Predis\Client($settings['sentinels'], $settings['options']);
    }else{
        $config     = [
            'scheme' => $settings['scheme'],
            'host' => $settings['host'],
            'port' => $settings['port'],
            'database' => $settings['database'],
        ];

        if (!empty($settings['password'])) {
            $config['password'] = $settings['password'];
        }
        if($config['scheme'] == 'tls'){
            $config['ssl'] = $settings['ssl'];
        }
        if(isset($settings['persistent'])){
            $config['persistent'] = $settings['persistent'];
        }

        return new Predis\Client($config);
    }
};

$container['notFoundHandler'] = function ($c) {
    return function ($req, $res) use ($c) {
        return $res->write('');
    };
};