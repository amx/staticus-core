<?php
namespace Staticus\Resources\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Diactoros\FileContentResponse\ResourceDoResponse;
use Staticus\Resources\Exceptions\SaveResourceErrorException;
use Staticus\Resources\Image\ImagePostProcessingAbstract;

abstract class ImageResizeMiddlewareAbstract extends ImagePostProcessingAbstract
{

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);

        $resourceDO = $this->resourceDO;
        if ($resourceDO->getSize()) {
            if ($resourceDO->isNew() // For POST method
                || $resourceDO->isRecreate() // For POST method
                || !is_file($resourceDO->getFilePath()) // For GET method
            ) {
                $targetResourceDO = $this->chooseTargetResource($response);

                $defaultImagePath = $targetResourceDO->getFilePath();
                if (is_file($defaultImagePath)) {
                    $this->resizeImage($defaultImagePath, $resourceDO->getFilePath(), $resourceDO->getWidth(), $resourceDO->getHeight());
                }
            }
        }

        $response = new ResourceDoResponse($resourceDO, $response->getStatusCode(), $response->getHeaders());

        return $next($request, $response);
    }

    public function resizeImage($sourcePath, $destinationPath, $width, $height)
    {
        $this->createDirectory(dirname($destinationPath));
        $imagick = $this->getImagick($sourcePath);
        $imagick->adaptiveResizeImage($width, $height, true);
        $imagick->writeImage($destinationPath);
    }
}