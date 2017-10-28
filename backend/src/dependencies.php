<?php
// DIC configuration

use SlashApp\LoggerProvider;
use SlashApp\Slim\ErrorFormatter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$container = $app->getContainer();

SlashApp\ExceptionErrorHandler::register();

(function ($c) {
	LoggerProvider::init();
})($container);

// view renderer
$container['renderer'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	$renderer = new Slim\Views\PhpRenderer($settings['template_path']);
	$renderer->addAttribute('base_uri', dirname($_SERVER['SCRIPT_NAME']) . '/');
	return $renderer;
};

// monolog
$container['logger'] = function ($c) {
	$settings = $c->get('settings')['logger'];
	return LoggerProvider::getLogger($settings['name']);
};

$container['phpErrorHandler'] = $container['errorHandler'] = function ($c) {
	return function (ServerRequestInterface $request, ResponseInterface $response, Throwable $exception) use ($c) {
		$formatter = new ErrorFormatter($c->get('settings')['displayErrorDetails']);
		$formatter->log($c->logger, $exception);
		return $formatter->renderJson($response, $exception);
	};
};

$container['notFoundHandler'] = function ($c) {
	return function (ServerRequestInterface $request, ResponseInterface $response) use ($c) {
		$handler = new \SlashApp\Slim\NotFound();
		return $handler($c->renderer, $request, $response);
	};
};

$container['notAllowedHandler'] = function ($c) {
	return function (ServerRequestInterface $request, ResponseInterface $response, $methods) use ($c) {
		$handler = new \SlashApp\Slim\MethodNotAllowed();
		return $handler($c->renderer, $request, $response, $methods);
	};
};

