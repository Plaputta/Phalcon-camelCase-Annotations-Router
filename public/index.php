<?php

mb_internal_encoding("UTF-8");

define('CONFIG_PROJECT_PATH','/var/www/testproject.dev/');

require 'init.php';

$response = $application->handle();

echo $response->getContent();