<?php
declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Pagination\Traits;

use Spiral\Pagination\CountableInterface;
use Spiral\Pagination\Exception\PaginationException;
use Spiral\Pagination\PaginatorInterface;

/**
 * Gives the ability to paginate object.
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
     * Paginate current selection using Paginator class.
     *
     * @param PaginatorInterface $paginator
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
     * @see setPaginator()
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
     * @param PaginatorInterface $paginator
     *
     * @return PaginatorInterface
     */
    private function preparePaginator(PaginatorInterface $paginator): PaginatorInterface
    {
        $this->prepared = true;
        if ($this instanceof \Countable && $paginator instanceof CountableInterface) {
            return $paginator->withCount($this->count());
        }

        return $paginator;
    }
}