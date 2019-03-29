<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Pagination;

interface PaginableInterface
{
    /**
     * Set the pagination limit.
     *
     * @param int $limit
     */
    public function limit(int $limit);

    /**
     * Set the pagination offset.
     *
     * @param int $offset
     */
    public function offset(int $offset);
}