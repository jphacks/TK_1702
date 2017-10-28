<?php
declare(strict_types=1);

namespace SlashApp\Slim;

use SlashApp\JsonRenderer;
use SlashApp\LoggerProvider;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use Slim\Views\PhpRenderer;

class ErrorFormatter
{
	/**
	 * @var bool
	 */
	private $displayErrorDetails;

	public function __construct(bool $display_error_details)
	{
		$this->displayErrorDetails = $display_error_details;
	}

	public function log(Logger $logger, \Throwable $exception)
	{
		$logger->error('uncaught error, ' . (string)$exception);
	}

	public function renderHtml(PhpRenderer $renderer, ResponseInterface $response, \Throwable $exception): ResponseInterface
	{
		$field = [
			'uid' => LoggerProvider::getUid()
		];
		if (IS_DEVELOPMENT) {
			$html = '<h2>Details</h2>';
			$html .= $this->formatSingleThrowableAsHtml($exception);

			while ($exception = $exception->getPrevious()) {
				$html .= '<h2>Previous exception</h2>';
				$html .= $this->formatSingleThrowableAsHtml($exception);
			}
			$field['error_html'] = $html;
		}
		$response = $this->resetBody($response)
			->withStatus(500);
		return $renderer->render($response, 'error.phtml', $field);
	}

	/**
	 * @param \Throwable $exception
	 * @return string
	 */
	protected function formatSingleThrowableAsHtml(\Throwable $exception): string
	{
		$html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));

		if (($code = $exception->getCode())) {
			$html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
		}
		if (($message = $exception->getMessage())) {
			$html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
		}
		if (($file = $exception->getFile())) {
			$html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
		}
		if (($line = $exception->getLine())) {
			$html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
		}
		if (($trace = $exception->getTraceAsString())) {
			$html .= '<h2>Trace</h2>';
			$html .= sprintf('<pre>%s</pre>', htmlentities($trace));
		}
		return $html;
	}

	public function renderJson(ResponseInterface $response, \Throwable $exception): ResponseInterface
	{
		$extra = [
			'uid' => LoggerProvider::getUid()
		];
		if (IS_DEVELOPMENT) {
			$extra['exception'] = $this->formatSingleThrowableAsJson($exception);
		}
		return JsonRenderer::create()->renderAsError($response, 500, 'Internal Server Error', null, $extra);
	}

	protected function formatSingleThrowableAsJson(\Throwable $exception)
	{
		$field = [
			'type' => get_class($exception)
		];

		if (($code = $exception->getCode())) {
			$field['code'] = $code;
		}
		if (($message = $exception->getMessage())) {
			$field['message'] = $message;
		}
		if (($file = $exception->getFile())) {
			$field['file'] = $file;
		}
		if (($line = $exception->getLine())) {
			$field['line'] = $line;
		}
		if (($trace = $exception->getTrace())) {
			foreach ($trace as $stack) {
				if (isset($stack['type'])) {
					unset($stack['type']);
				}
				if (isset($stack['args'])) {
					$args = [];
					foreach ($stack['args'] as $arg) {
						$args[] = $this->formatTraceArgument($arg);
					}
					$stack['args'] = $args;
				}
				$field['trace'][] = $stack;
			}
		}
		if (($cause = $exception->getPrevious())) {
			$field['cause'] = $cause;
		}
		return $field;
	}

	protected function formatTraceArgument($arg)
	{
		if (is_object($arg)) {
			return sprintf('Object(%s)', get_class($arg));
		} else if (is_array($arg)) {
			$ret = [];
			foreach ($arg as $index => $element) {
				$ret[$index] = $this->formatTraceArgument($element);
			}
			return $ret;
		} else {
			return $arg;
		}
	}

	protected function resetBody(ResponseInterface $response): ResponseInterface
	{
		$body = new Body(fopen('php://temp', 'r+'));
		return $response->withBody($body);
	}
}
