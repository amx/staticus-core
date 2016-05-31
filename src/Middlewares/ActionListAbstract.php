<?php
namespace Staticus\Middlewares;

use League\Flysystem\FilesystemInterface;
use Staticus\Exceptions\NotFoundException;
use Staticus\Resources\Commands\FindVersionsResourceCommand;
use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Resources\File\ResourceDO;
use Zend\Diactoros\Response\JsonResponse;

abstract class ActionListAbstract extends MiddlewareAbstract
{
    protected $actionResult = [];
    /**
     * @var ResourceDOInterface|ResourceDO
     */
    protected $resourceDO;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct(
        ResourceDOInterface $resourceDO
        , FilesystemInterface $filesystem
    )
    {
        $this->resourceDO = $resourceDO;
        $this->filesystem = $filesystem;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return EmptyResponse
     * @throws \Exception
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        parent::__invoke($request, $response, $next);

        $this->action();
        $this->response = new JsonResponse($this->actionResult);

        return $this->next();
    }

    protected function action()
    {
        $this->actionResult['current'] = $this->findCurrentResoure();
        $this->actionResult['versions'] = $this->findVersions();
    }

    protected function findVersions()
    {
        $command = new FindVersionsResourceCommand($this->resourceDO, $this->filesystem);
        $versions = $command();
        sort($versions);

        return $versions;
    }

    protected function findCurrentResoure()
    {
        $filePath = realpath($this->resourceDO->getFilePath());
        if (!$this->filesystem->has($filePath)) {

            throw new NotFoundException('Resource "' . $this->resourceDO->getName() . '.' . $this->resourceDO->getType() . '" is not found');
        }
        $current = $this->resourceDO->toArray();

        return $current;
    }
}