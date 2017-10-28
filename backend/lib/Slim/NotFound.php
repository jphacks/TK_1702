<?php
declare(strict_types=1);

namespace SlashApp\Slim;


use SlashApp\JsonRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Body;
use Slim\Views\PhpRenderer;

class NotFound
{
	public function __invoke(
		PhpRenderer $renderer,
		ServerRequestInterface $request,
		ResponseInterface $response
	): ResponseInterface {
		$response = $response->withStatus(404);

		return $this->renderJson($request, $response);
	}

	protected function renderJson(
		ServerRequestInterface $request,
		ResponseInterface $response
	): ResponseInterface {
		return JsonRenderer::create()
			->renderAsError($response, 'Not Found', 404);
	}
}
