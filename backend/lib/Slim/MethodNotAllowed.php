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

		if (is_api_endpoint($request)) {
			return $this->renderJson($request, $response, $methods);
		} else {
			return $this->renderHtml($renderer, $request, $response, $methods);
		}
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
				->renderAsError($response, 405, 'Method Not Allowed',
					sprintf('%s is not allowed', $request->getMethod()), ['allowed_methods' => $methods]);
		}
	}

	protected function renderHtml(
		PhpRenderer $renderer,
		ServerRequestInterface $request,
		ResponseInterface $response,
		$methods
	): ResponseInterface {
		return $renderer->render($response, 'method_not_allowed.phtml', ['allow' => implode(', ', $methods)]);
	}

	protected function isOptionMethodRequested(ServerRequestInterface $request)
	{
		return $request->getMethod() === 'OPTIONS';
	}
}
