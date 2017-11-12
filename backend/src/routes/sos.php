<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\Constants;
use SlashApp\JsonRenderer;
use SlashApp\Slim\Middleware\AuthMiddleware;
use Propel\Runtime\ActiveQuery\Criteria;
use Slim\Http\UploadedFile;

$app->group('/sos', function () {
	$this->post('', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
		$user = $request->getAttribute('user');
		$user_id = $user->getUserId();

        /** @var \ORM\LocationHistory $locationHistory */
        $locationHistory = \ORM\LocationHistoryQuery::create()
            ->filterByUserId($user_id)
            ->withLatLong()
            ->orderByCreatedAt(Criteria::DESC)
            ->findOne();

        $lineId = $user->getLineId() ?? Constants::getLineReceiverId();

        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(Constants::getLineToken());
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => Constants::getLineChannelSecret()]);

        $locationMessageBuilder = new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder(
			    'SOSが発信されました!',
			    '発信者の身に危険が迫っている可能性があります。発信者の無事が確認できなければ今すぐ110番に通報をしましょう！',
			    $locationHistory->getLatitude(),
			    $locationHistory->getLongitude()
        );

        $bot->pushMessage($lineId, $locationMessageBuilder);

        return JsonRenderer::create()->render($response, ['status' => 'ok']);
	});

});
