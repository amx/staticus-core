<?php
namespace Staticus\Resources\Middlewares;

use Staticus\Resources\File\ResourceDO;
use Staticus\Resources\ResourceDOInterface;
use Staticus\Resources\ResourceImageDO;

abstract class SaveImageMiddlewareAbstract extends SaveResourceMiddlewareAbstract
{
    protected function copyFileToDefaults(ResourceDOInterface $resourceDO)
    {
        /** @var ResourceImageDO $resourceDO */
        if (ResourceDO::DEFAULT_VARIANT !== $resourceDO->getVariant()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVariant();
            $defaultDO->setVersion();
            $defaultDO->setWidth();
            $defaultDO->setHeight();
            $this->copyResource($resourceDO, $defaultDO);
        }
        if (ResourceDO::DEFAULT_VERSION !== $resourceDO->getVersion()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setVersion();
            $defaultDO->setWidth();
            $defaultDO->setHeight();
            $this->copyResource($resourceDO, $defaultDO);
        }
        if (ResourceImageDO::DEFAULT_SIZE !== $resourceDO->getSize()) {
            $defaultDO = clone $resourceDO;
            $defaultDO->setWidth();
            $defaultDO->setHeight();
            $this->copyResource($resourceDO, $defaultDO);
        }
    }
}
