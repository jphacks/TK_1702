<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;
use SlashApp\Slim\Middleware\AuthMiddleware;
use Propel\Runtime\ActiveQuery\Criteria;
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
			$thumbname = str_replace("video", "thumb", substr($filename, 0, -3)."jpg");
			$arr = explode("/", $filename);
			$video_uri = \SlashApp\Constants::getUrlBase().\SlashApp\Constants::getStaticActualDirectory().'/video/'. end($arr);
			$thumb_uri = \SlashApp\Constants::getUrlBase().\SlashApp\Constants::getStaticActualDirectory().'/thumb/'. substr(end($arr), 0, -3)."jpg";

			$video = new \ORM\Video();
			$video->setOwnerId($user_id)
				->setFileName($filename)
				->setThumbName($thumbname)
				->save();

			$locationHistory = \ORM\LocationHistoryQuery::create()
				->orderByCreatedAt(Criteria::DESC)
				->withLatLong()
				->findOneByUserId($user_id);


			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(\SlashApp\Constants::getLineToken());
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => \SlashApp\Constants::getLineChannelSecret()]);

			$locationMessageBuilder = new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder(
			    '危険が迫っている可能性があります',
			    'ご確認お願いします',
			    $locationHistory->getLatitude(),
			    $locationHistory->getLongitude()
			);

			$bot->pushMessage(\SlashApp\Constants::getLineReceiverId(), $locationMessageBuilder);
//

			$videoMessageBuilder = new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder(
				$video_uri,
				$thumb_uri
			);
			$bot->pushMessage(\SlashApp\Constants::getLineReceiverId(), $videoMessageBuilder);


			return JsonRenderer::create()->render($response, ['status' => 'ok']);
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

    $this->delete('', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
        $user = $request->getAttribute('user');
	    $body = $request->getParsedBody();
        	$video_id = $body['id'] ?? null;
		$user_id = $user->getUserId();
		$videos = \ORM\VideoQuery::create()
			->filterByOwnerId($user_id)
			->filterById($video_id)
            ->delete();
        return JsonRenderer::create()->render($response, ['status' => 'ok']);
    });
});

function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
	$basename = bin2hex(random_bytes(32));
	$filename = sprintf('%s.mp4', $basename);

	$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

	return $filename;
}
