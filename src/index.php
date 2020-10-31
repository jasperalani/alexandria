<?php

require __DIR__ . '/../vendor/autoload.php';

include_once 'Controllers/Error.php';
include_once 'Controllers/Request.php';
include_once 'Controllers/Retrieve.php';
include_once 'Controllers/Upload.php';

include_once 'CorsMiddleware.php';
include_once 'ErrorRenderer.php';
include_once 'Alexandria.php';

$compressionAvailable = file_exists('pngquant.php');
if($compressionAvailable){
	include_once 'pngquant.php';
}

new \Alexandria\Alexandria($compressionAvailable, 'production');