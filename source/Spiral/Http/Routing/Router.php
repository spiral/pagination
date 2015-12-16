<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing;

use Cocur\Slugify\SlugifyInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Http\Exceptions\BadRouteException;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Exceptions\RouterException;

/**
 * Spiral implementation of RouterInterface.
 */
class Router implements RouterInterface
{
    /**
     * Every route should be executed in a context of base path.
     *
     * @var string
     */
    private $basePath = '/';

    /**
     * @var RouteInterface[]
     */
    private $routes = [];

    /**
     * Primary route (fallback if no routes work).
     *
     * @var RouteInterface
     */
    private $defaultRoute = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * {@inheritdoc}
     *
     * @param RouteInterface|array $default Default route or options to construct instance of
     *                                      DirectRoute.
     * @param bool                 $keepOutput
     * @throws RouterException
     */
    public function __construct(ContainerInterface $container, $basePath = '/')
    {
        $this->basePath = $basePath;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        //Open router scope
        $scope = $this->container->replace(self::class, $this);

        $route = $this->findRoute($request, $this->basePath);

        if (empty($route)) {
            //Unable to locate route
            throw new ClientException(ClientException::NOT_FOUND);
        }

        //Default routes will understand about keepOutput
        $response = $route->perform(
            $request->withAttribute('route', $route),
            $response,
            $this->container
        );

        //Close router scope
        $this->container->restore($scope);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function addRoute(RouteInterface $route)
    {
        $this->routes[] = $route;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultRoute(RouteInterface $route)
    {
        $this->defaultRoute = $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute($name)
    {
        foreach ($this->routes as $route) {
            if ($route->getName() == $name) {
                return $route;
            }
        }

        throw new BadRouteException("Undefined route '{$name}'.");
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     *
     * @todo Optimize performance of slugification and uri compilation
     * @throws BadRouteException
     */
    public function uri($route, $parameters = [], SlugifyInterface $slugify = null)
    {
        //todo: resolve SlugifyInterface using container

        try {
            return $this->getRoute($route)->uri($parameters, $this->basePath, $slugify);
        } catch (BadRouteException $e) {
            //Using fallback uri
        }

        //Will be handled via default route where route name is specified as controller::action
        if (strpos($route, RouteInterface::SEPARATOR) === false && strpos($route, '/') === false) {
            throw new BadRouteException(
                "Unable to locate route or use default route with controller::action pattern."
            );
        }

        //We can fetch controller and action names from url
        list($controller, $action) = explode(
            RouteInterface::SEPARATOR,
            str_replace('/', RouteInterface::SEPARATOR, $route)
        );

        return $this->defaultRoute->uri(
            compact('controller', 'action') + $parameters,
            $this->basePath,
            $slugify
        );
    }

    /**
     * Find route matched for given request.
     *
     * @param ServerRequestInterface $request
     * @param string                 $basePath
     * @return null|RouteInterface
     */
    protected function findRoute(ServerRequestInterface $request, $basePath)
    {
        foreach ($this->routes as $route) {
            if ($route->match($request, $basePath)) {
                return $route;
            }
        }

        if (!empty($this->defaultRoute) && $this->defaultRoute->match($request, $basePath)) {
            return $this->defaultRoute;
        }

        return null;
    }
}