<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;

$app->post('/fcm', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
	$data = $request->getParsedBody();
	/** @var \ORM\User $user */
	$user = $request->getAttribute('user');
	$token = $data['token'] ?? null;

	if (empty($token)) {
		return JsonRenderer::create()->renderAsError($response, 'token is empty', 400);
	}

	$user->setFcmToken($token)
		->save();

	return JsonRenderer::create()->render($response, ['status' => 'ok']);
});
