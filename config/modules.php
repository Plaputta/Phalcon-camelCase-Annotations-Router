<?php

$application->registerModules(array(
    'frontend' => array(
    		'className' => 'TestProject\Frontend\Module',
    		'path' => CONFIG_PROJECT_PATH . 'apps/frontend/Module.php'
    )
));