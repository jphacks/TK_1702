<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;
use SlashApp\Slim\Middleware\AuthMiddleware;
use Slim\Http\UploadedFile;

$container = $app->getContainer();
$container['upload_directory'] = CH_BASE_DIR.'/static/video';

$app->group('/video', function() {
    $this->post('', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
        $user = $request->getAttribute('user');
        $user_id = $user->getUserId();
        $tempfile = $_FILES['video']['tmp_name'];
        $directory = $this->get('upload_directory');

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['video'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);

            $video = new \ORM\Video();
            $video->setOwnerId($user_id)
                ->setFileName($filename)
                ->setThumbName(substr($filename,0,-3)."jpg")
                ->save();
            
            return JsonRenderer::create()->render($response, ['message'=>'ok']);
        } else {
            return JsonRenderer::create()->renderAsError($response, 'Upload Failure', 400);
        }
    });

    $this->get('', function(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $directory = $this->get('upload_directory');
        $user = $request->getAttribute('user');
        $user_id = $user->getUserId();
        $videos = \ORM\VideoQuery::create()
                ->filterByOwnerId($user_id)
                ->find();

        return JsonRenderer::create()->render($response, objToArr($videos, '/static/video'));
    });

});

function objToArr($obj, $directory) 
{
	$arr = [];
	foreach ($obj as $value) {
		$piyo = [];
		$piyo["created_at"] = $value->getCreatedAt();
		$piyo["owner_id"] = $value->getOwnerId();
		$piyo["file_name"] = $directory.DIRECTORY_SEPARATOR.$value->getFileName();
		$piyo["thumb_name"] = $directory.DIRECTORY_SEPARATOR.$value->getThumbName();
		$arr[] = $piyo;
	}
	return $arr;
}

function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $basename = bin2hex(random_bytes(32));
    $filename = sprintf('%s.mov', $basename);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}
