<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;
use SlashApp\Slim\Middleware\AuthMiddleware;
use SlashApp\Slim\Http\UploadedFile;

$container = $app->getContainer();
$container['upload_directory'] = __DIR__.'../../../uploads';

$app->group('/video', function() {
    $this->post('', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
        $udid = $response->getHeaderString('X-UDID');
        $user_id = \ORM\UserQuery::create()
            ->findOneByUdid($udid);

        $tempfile = $_FILES['video']['tmp_name'];
        $directory = $this->get('upload_directory');

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['video'];

        if ($uploadedFiles->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);

            $video = new \ORM\Video()
                ->setOwnerId($user_id)
                ->setFileName($filename)
                ->setThumbName(substr($filename,0,-3,"jpg"))
                ->save();
            
            return JsonRenderer::create()->render($response, ['message'=>'ok']);
        } else {
            return JsonRenderer::create()->renderAsError($response, 'Upload Failure', 400);
        }
    });

    $this->get('', function(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $udid = $response->getHeaderString('X-UDID');
        $user_id = \ORM\UserQuery::create()
            ->findOneByUdid($udid)
            ->delete();
        $videos = \ORM\VideoQuery::create()
                ->filterByUserId($user_id)
                ->find();

        return get_renderer()->render($response, $videos);
    });

});

function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $basename = bin2hex(random_bytes(32));
    $filename = sprintf('%s.mov', $basename);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}
