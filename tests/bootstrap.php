<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->addPsr4('App\\', 'vfs://testRoot/src/');
