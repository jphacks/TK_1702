<?php
declare(strict_types=1);

namespace Chat\Slim;


use Chat\JsonRenderer;
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

		if (is_api_endpoint($request)) {
			return $this->renderJson($request, $response);
		} else {
			return $this->renderHtml($renderer, $request, $response);
		}
	}

	protected function renderJson(
		ServerRequestInterface $request,
		ResponseInterface $response
	): ResponseInterface {
		return JsonRenderer::create()
			->renderAsError($response, 404, 'Not Found');
	}

	protected function renderHtml(
		PhpRenderer $renderer,
		ServerRequestInterface $request,
		ResponseInterface $response
	): ResponseInterface {
		return $renderer->render($response, 'not_found.phtml');
	}
}
