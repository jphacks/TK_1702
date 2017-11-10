<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\Constants;
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
			$basename = bin2hex(random_bytes(32));
			$video_temp = Constants::getStaticActualDirectory() . '/video/' . $basename . '.mov';
			$video_file = Constants::getStaticActualDirectory() . '/video/' . $basename . '.mp4';
			$thumb_file = Constants::getStaticActualDirectory() . '/thumb/' . $basename . '.jpg';

			$uploadedFile->moveTo($video_temp);

			$cmd = sprintf('ffmpeg -i %s -c:v copy -c:a copy %s',
				escapeshellarg($video_temp), escapeshellarg($video_file));
			$errorfp = fopen('/tmp/jphacks2017.log', 'w');
			$desc = [
				0 => ['file', '/dev/null', 'r'],
				1 => $errorfp,
				2 => $errorfp
			];
			$subproc = proc_open($cmd, $desc, $pipes);
			if (is_resource($subproc)) {
				$return_value = proc_close($subproc);
				if ($return_value) {
					return JsonRenderer::create()->renderAsError($response, 'encode failed', 500);
				}
			} else {
				return JsonRenderer::create()->renderAsError($response, 'encode failed', 500);
			}

			unlink($video_temp);

			$cmd = sprintf('ffmpeg -i %s -ss 00:00:01 -vframes 1 %s',
				escapeshellarg($video_file), escapeshellarg($thumb_file));
			$desc = [
				0 => ['file', '/dev/null', 'r'],
				1 => $errorfp,
				2 => $errorfp
			];
			$subproc = proc_open($cmd, $desc, $pipes);
			if (is_resource($subproc)) {
				$return_value = proc_close($subproc);
				if ($return_value) {
					return JsonRenderer::create()->renderAsError($response, 'encode failed', 500);
				}
			} else {
				return JsonRenderer::create()->renderAsError($response, 'encode failed', 500);
			}

			$video_uri = Constants::getUrlBase() . '/' . Constants::getStaticBasePath() . '/video/' . $basename . '.mp4';
			$thumb_uri = Constants::getUrlBase() . '/' . Constants::getStaticBasePath() . '/thumb/' . $basename . '.jpg';
			\SlashApp\LoggerProvider::getLogger('hoge')->debug($video_uri);
			$video = new \ORM\Video();
			$video->setOwnerId($user_id)
				->setFileName($basename . '.mp4')
				->setThumbName($basename . '.jpg')
				->save();

			$locationHistory = \ORM\LocationHistoryQuery::create()
				->orderByCreatedAt(Criteria::DESC)
				->withLatLong()
				->findOneByUserId($user_id);


			$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(Constants::getLineToken());
			$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => Constants::getLineChannelSecret()]);

			$locationMessageBuilder = new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder(
			    '危険が迫っている可能性があります',
			    'ご確認お願いします',
			    $locationHistory->getLatitude(),
			    $locationHistory->getLongitude()
			);

			$bot->pushMessage(Constants::getLineReceiverId(), $locationMessageBuilder);
//

			$videoMessageBuilder = new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder(
				$video_uri,
				$thumb_uri
			);
			$bot->pushMessage(Constants::getLineReceiverId(), $videoMessageBuilder);


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
		\ORM\VideoQuery::create()
			->filterByOwnerId($user_id)
			->filterByVideoId($video_id)
            ->delete();
        return JsonRenderer::create()->render($response, ['status' => 'ok']);
    });
});
