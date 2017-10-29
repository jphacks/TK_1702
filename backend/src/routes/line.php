<?php
declare(strict_types=1);

$app->post('/line', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $data = $request->getParsedBody();

    $groupId = $data['source']['groupId'] ?? null;
    if($groupId) {
        file_put_contents("ids", $groupId);
    }

    return JsonRenderer::create()->render($response, []);
});
