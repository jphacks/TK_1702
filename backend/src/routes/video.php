<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;
use SlashApp\Slim\Middleware\AuthMiddleware;
use Slim\Http\UploadedFile;

$app->group('/video', function () {
	$this->post('', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
		$user = $request->getAttribute('user');
		$user_id = $user->getUserId();

		$uploadedFiles = $request->getUploadedFiles();
		/** @var UploadedFile $uploadedFile */
		$uploadedFile = $uploadedFiles['video'];

		if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
			$filename = moveUploadedFile(\SlashApp\Constants::getStaticActualDirectory() . '/video', $uploadedFile);

			$video = new \ORM\Video();
			$video->setOwnerId($user_id)
				->setFileName($filename)
				->setThumbName(substr($filename, 0, -3) . "jpg")
				->save();

            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(\SlashApp\Constants::getLineToken());
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => \SlashApp\Constants::getLineChannelSecret()]);

            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('危険が迫っている可能性があります');
            $bot->pushMessage(\SlashApp\Constants::getLineReceiverId(), $textMessageBuilder);

			return JsonRenderer::create()->render($response, ['message' => 'ok']);
		} else {
			return JsonRenderer::create()->renderAsError($response, 'Upload Failure', 400);
		}
	});

	$this->get('', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
		$user = $request->getAttribute('user');
		$user_id = $user->getUserId();
		$videos = \ORM\VideoQuery::create()
			->filterByOwnerId($user_id)
			->find();
		return JsonRenderer::create()->render($response, format_as_api($videos));
	});

});

function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
	$basename = bin2hex(random_bytes(32));
	$filename = sprintf('%s.mov', $basename);

	$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

	return $filename;
}
