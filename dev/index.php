<?php

declare(strict_types=1);

use Freema\GA4MeasurementProtocolBundle\Dev\DevKernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

Debug::enable();

$kernel = new DevKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);