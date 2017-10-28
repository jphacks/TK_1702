<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
use SlashApp\Slim\Middleware\AuthMiddleware;

$app->add(new AuthMiddleware());
