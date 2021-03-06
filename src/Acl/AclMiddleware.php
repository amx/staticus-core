<?php
namespace Staticus\Acl;

use Staticus\Auth\User;
use Staticus\Auth\UserInterface;
use Staticus\Config\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staticus\Diactoros\Response\ResourceDoResponse;
use Staticus\Exceptions\WrongRequestException;
use Staticus\Resources\Middlewares\PrepareResourceMiddlewareAbstract;
use Staticus\Resources\ResourceDOInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Stratigility\MiddlewareInterface;

class AclMiddleware implements MiddlewareInterface
{
    protected $config;

    /**
     * @var AclServiceInterface|AclService
     */
    protected $service;

    /**
     * @var UserInterface|User
     */
    protected $user;

    public function __construct(ConfigInterface $config, AclServiceInterface $service, UserInterface $user)
    {
        $this->config = $config->get('acl');
        $this->service = $service;
        $this->user = $user;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    )
    {
        /** @var $response ResourceDoResponse */
        $this->checkResponseOrDie($response);
        $action = $this->getAction($request);
        $resourceDO = $response->getContent();
        $resourceNamespace = $resourceDO->getNamespace();
        $userNamespace = $this->user->getNamespace();
        $AclResourceCommon = get_class($resourceDO);
        $AclResourceUnique = $resourceDO instanceof ResourceInterface
            ? $resourceDO->getResourceId()
            : null;

        if (
            // User have access to this type of resources regardless namespaces
            $this->isAllowedForUser($AclResourceCommon, $action, '')

            // User have access to this unique resource regardless namespaces
            || $this->isAllowedForUser($AclResourceUnique, $action, '')

            // User have access to this resource type in common namespace
            || (
                !$resourceNamespace
                && $this->isAllowedForUser($AclResourceCommon, $action, ResourceDOInterface::NAMESPACES_WILDCARD)
            )

            // User have access to this resource type in concrete selected namespace
            || (
                $resourceNamespace
                && $this->isAllowedForUser($AclResourceCommon, $action, $resourceNamespace)
            )
            || (
                // This is a user home namespace
                $resourceNamespace === $userNamespace

                // User have access to the current action in his own namespace
                && $this->isAllowedForUser($AclResourceCommon, $action, UserInterface::NAMESPACES_WILDCARD)
            )
            || (
                // This is an another user namespace
                $resourceNamespace !== $userNamespace
                && 0 === strpos($resourceNamespace, UserInterface::NAMESPACES)

                // User have access to the current action in his own namespace
                && $this->isAllowedForGuest($AclResourceCommon, $action, UserInterface::NAMESPACES_WILDCARD)
            )
        ) {

            return $next($request, $response);
        }

        return new EmptyResponse(403);
    }


    /**
     * @param ResponseInterface $response
     */
    protected function checkResponseOrDie(ResponseInterface $response)
    {
        if (!$this->isSupportedResponse($response)) {

            // something like PrepareResourceMiddleware should be called before this
            throw new WrongRequestException(
                'Unsupported type of the response for ACL. Resource preparing layer must be called before this.');
        }
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    protected function isSupportedResponse(ResponseInterface $response)
    {
        return $response instanceof ResourceDoResponse;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getAction(ServerRequestInterface $request)
    {
        if (PrepareResourceMiddlewareAbstract::getParamFromRequest(Actions::ACTION_SEARCH, $request)) {

            return Actions::ACTION_SEARCH;
        }
        if (PrepareResourceMiddlewareAbstract::getParamFromRequest(Actions::ACTION_LIST, $request)) {

            return Actions::ACTION_LIST;
        }

        $method = $request->getMethod();
        switch ($method) {
            case 'GET':
                $action = Actions::ACTION_READ;
                break;
            case 'POST':
                $action = Actions::ACTION_WRITE;
                break;
            case 'DELETE':
                $action = Actions::ACTION_DELETE;
                break;
            default:
                throw new WrongRequestException('Unknown access control action');
        }

        return $action;
    }

    protected function isAllowedForUser($aclResource, $action, $namespace = '')
    {
        if (!$this->service->acl()->hasResource($namespace . $aclResource)) {

            return false;
        }

        return $this->user->can($namespace . $aclResource, $action);
    }

    protected function isAllowedForGuest($aclResource, $action, $namespace = '')
    {
        if (!$aclResource || !$this->service->acl()->hasResource($namespace . $aclResource)) {

            return false;
        }

        return $this->service->acl()->isAllowed(Roles::GUEST, $namespace . $aclResource, $action);
    }
}