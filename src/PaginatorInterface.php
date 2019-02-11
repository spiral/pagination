<?php
declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Pagination;

/**
 * Generic paginator interface with ability to set/get page and limit values.
 */
interface PaginatorInterface
{
    /**
     * Paginate the target selection.
     *
     * @param PaginableInterface $target
     * @return PaginableInterface
     */
    public function paginate(PaginableInterface $target): PaginableInterface;
}