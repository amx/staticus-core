<?php
namespace Staticus\Resources\Middlewares\Image;

use Staticus\Resources\Middlewares\ResourceResponseMiddlewareAbstract;
use Staticus\Resources\ResourceDOInterface;
use Staticus\Resources\Image\ResourceImageDO;

abstract class ImageResponseMiddlewareAbstract extends ResourceResponseMiddlewareAbstract
{
    protected function getUri(ResourceDOInterface $resourceDO)
    {
        /** @var \Staticus\Resources\Image\ResourceImageDO $resourceDO */
        $uri = $resourceDO->getName() . '.' . $resourceDO->getType();
        $query = [];
        if (ResourceDOInterface::DEFAULT_VARIANT !== $resourceDO->getVariant()) {
            $query['var'] = $resourceDO->getVariant();
        }
        if (ResourceDOInterface::DEFAULT_VERSION !== $resourceDO->getVersion()) {
            $query['v'] = $resourceDO->getVersion();
        }
        if (ResourceImageDO::DEFAULT_DIMENSION !== $resourceDO->getDimension()) {
            $query['size'] = $resourceDO->getDimension();
        }
        $query = http_build_query($query);
        if ($query) {
            $uri .= '?' . $query;
        }

        return $uri;
    }
}
