<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Pagination\Traits;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Pagination\CountingInterface;
use Spiral\Pagination\Exception\PaginationException;
use Spiral\Pagination\PaginatorInterface;
use Spiral\Pagination\PaginatorsInterface;

/**
 * Provides ability to paginate associated instance. Trait is able to automatically create paginator using active
 * container scope.
 *
 * Compatible with PaginatorAwareInterface.
 */
trait PaginatorTrait
{
    /**
     * @internal
     *
     * @var PaginatorInterface|null
     */
    private $paginator;

    /**
     * Indication that pagination was already counted.
     *
     * @var bool
     */
    private $prepared = false;

    /**
     * Indication that object was paginated.
     *
     * @return bool
     */
    public function hasPaginator(): bool
    {
        return $this->paginator instanceof PaginatorInterface;
    }

    /**
     * Manually set paginator instance for specific object.
     *
     * @param PaginatorInterface $paginator
     *
     * @return $this
     */
    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Get paginator for the current selection. Paginator will be automatically configured with
     * count value if parent countable.
     *
     * @see hasPaginator()
     * @see paginate()
     *
     * @param bool $prepare Set to true to calculate pagination window.
     *
     * @return PaginatorInterface
     */
    public function getPaginator(bool $prepare = true): PaginatorInterface
    {
        if (!$this->hasPaginator()) {
            throw new PaginationException("Unable to get paginator, no paginator were set");
        }

        if (!$this->prepared || $prepare) {
            $this->paginator = $this->preparePaginator($this->paginator);
        }

        return $this->paginator;
    }

    /**
     * Paginate current selection using Paginator class.
     *
     * @param int    $limit     Pagination limit.
     * @param string $parameter Name of parameter to associate paginator with, by default query parameter of active
     *                          request to be used.
     *
     * @deprecated
     * @return $this
     * @throws ScopeException
     */
    public function paginate(int $limit = 25, string $parameter = 'page')
    {
        $container = ContainerScope::getContainer();

        if (empty($container) || !$container->has(PaginatorsInterface::class)) {
            throw new ScopeException(
                'Unable to create paginator, `PaginatorsInterface` binding is missing or container scope is not set'
            );
        }

        //Now we can create new instance of paginator using factory
        $this->paginator = $container->get(PaginatorsInterface::class)->createPaginator($parameter, $limit);

        return $this;
    }

    /**
     * @param PaginatorInterface $paginator
     *
     * @return PaginatorInterface
     */
    private function preparePaginator(PaginatorInterface $paginator): PaginatorInterface
    {
        $this->prepared = true;
        if ($this instanceof \Countable && $paginator instanceof CountingInterface) {
            return $paginator->withCount($this->count());
        }

        return $paginator;
    }
}