#!/usr/bin/env php
<?php

include_once 'vendor/autoload.php';

$app = new \Symfony\Component\Console\Application();

$app->add(new \W7\Validate\Ide\Helper\IdeHelperCommand());

$app->run();