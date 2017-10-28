<?php
declare(strict_types=1);

namespace SlashApp;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;

/**
 * Class JsonRenderer
 *
 * Render PHP view scripts into a PSR-7 Response object
 */
class JsonRenderer
{
	public static function create(): JsonRenderer
	{
		return new JsonRenderer();
	}

	/**
	 * JsonRenderer constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Render a template
	 *
	 * @param ResponseInterface $response
	 * @param array $data
	 * @param int $status
	 * @return ResponseInterface
	 */
	public function render(ResponseInterface $response, array $data = [], int $status = 200): ResponseInterface
	{
		$body = new Body(fopen('php://temp', 'r+'));
		$body->write(json_encode($data));
		return $response
			->withBody($body)
			->withHeader('Content-Type', 'application/json;charset=utf-8');
	}

	/**
	 * Render error
	 *
	 * @param ResponseInterface $response
	 * @param int $status_code
	 * @param string $message
	 * @return ResponseInterface
	 */
	public function renderAsError(
		ResponseInterface $response,
		string $message,
		int $status_code,
		array $extra = null
	): ResponseInterface
	{
		$response = $response->withStatus($status_code);
		$data = ['error' => $message];
		if ($extra !== null && IS_DEVELOPMENT) {
			$data['extra'] = $extra;
		}
		$body = new Body(fopen('php://temp', 'r+'));
		$body->write(json_encode($data));
		return $response
			->withStatus($status_code)
			->withBody($body)
			->withHeader('Content-Type', 'application/json;charset=utf-8');
	}
}
