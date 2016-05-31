<?php
namespace Staticus\Resources\Middlewares\Image;

use Staticus\Diactoros\Response\ResourceDoResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\Image\CropImageDOInterface;

abstract class ImageCropMiddlewareAbstract extends ImagePostProcessingMiddlewareAbstract
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);
        if (!$this->isSupportedResponse($response)) {

            return $next($request, $response);
        }
        $resourceDO = $this->resourceDO;
        $crop = $resourceDO->getCrop();
        if ($resourceDO->getSize() && $crop) {
            if ($resourceDO->isNew() // For POST method
                || $resourceDO->isRecreate() // For POST method
                || !$this->filesystem->has($resourceDO->getFilePath()) // For GET method
            ) {
                $targetResourceDO = $this->chooseTargetResource($response);

                $defaultImagePath = $targetResourceDO->getFilePath();
                if ($this->filesystem->has($defaultImagePath)) {
                    $this->cropImage($defaultImagePath, $resourceDO->getFilePath(), $crop);
                }
            }
        }
        $response = new ResourceDoResponse($resourceDO, $response->getStatusCode(), $response->getHeaders());

        return $next($request, $response);
    }

    public function cropImage($sourcePath, $destinationPath, CropImageDOInterface $crop)
    {
        $this->createDirectory(dirname($destinationPath));
        $imagick = $this->getImagick($sourcePath);
        $imagick->cropImage(
            $crop->getWidth(),
            $crop->getHeight(),
            $crop->getX(),
            $crop->getY()
        );
        $imagick->writeImage($destinationPath);
        $imagick->clear();
        $imagick->destroy();
    }
}