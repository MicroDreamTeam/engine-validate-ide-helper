#!/usr/bin/env php
<?php

include_once 'vendor/autoload.php';

$app = new \Symfony\Component\Console\Application();

$app->add(new \Itwmw\Validate\Ide\Helper\IdeHelperCommand());

$app->run();