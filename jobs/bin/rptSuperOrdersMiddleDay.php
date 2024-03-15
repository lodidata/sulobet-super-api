<?php

global $app;
$obj = new Logic\Game\GameApi($app->getContainer());
var_dump($obj->rptSuperOrdersMiddleDay());
