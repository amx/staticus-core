<?php
namespace Staticus\Resources\Png;

use Staticus\Resources\ImagePostProcessingMiddlewareAbstract;

class ImagePostProcessingMiddleware extends ImagePostProcessingMiddlewareAbstract
{
    public function __construct(ResourceDO $resourceDO)
    {
        parent::__construct($resourceDO);
    }
}