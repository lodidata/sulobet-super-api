<?php

if (count($argv) < 2) {
    echo "参数不合法\n\r";
    return;
}
global $app;
$day_num = $argv[2] ?? -1;
$obj = new Logic\Game\GameApi($app->getContainer());
var_dump($obj->rptSuperOrdersMiddleDayRevise($day_num));
