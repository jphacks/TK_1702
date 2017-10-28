<?php
declare(strict_types=1);

namespace SlashApp\Slim;


use SlashApp\JsonRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Body;
use Slim\Views\PhpRenderer;

class MethodNotAllowed
{
	public function __invoke(
		PhpRenderer $renderer,
		ServerRequestInterface $request,
		ResponseInterface $response,
		$methods
	): ResponseInterface {
		$response = $response->withStatus($this->isOptionMethodRequested($request) ? 200 : 405)
			->withHeader('Allow', implode(', ', $methods));

		return $this->renderJson($request, $response, $methods);
	}

	protected function renderJson(
		ServerRequestInterface $request,
		ResponseInterface $response,
		$methods
	): ResponseInterface {
		if ($this->isOptionMethodRequested($request)) {
			return JsonRenderer::create()
				->render($response, ['methods' => $methods]);
		} else {
			return JsonRenderer::create()
				->renderAsError($response, 'Method Not Allowed', 405);
		}
	}

	protected function isOptionMethodRequested(ServerRequestInterface $request)
	{
		return $request->getMethod() === 'OPTIONS';
	}
}
