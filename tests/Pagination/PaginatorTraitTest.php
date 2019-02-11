<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Pagination\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Pagination\Exception\PaginationException;
use Spiral\Pagination\Paginator;
use Spiral\Pagination\PaginatorsInterface;
use Spiral\Pagination\Traits\PaginatorTrait;

/**
 * Class PaginatorTraitTest
 *
 * @package Spiral\Tests\Pagination\Traits
 */
class PaginatorTraitTest extends TestCase
{
    const PAGINATOR_LIMIT     = 10;
    const PAGINATOR_COUNT     = 15;
    const PAGINATOR_PARAMETER = 'test';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PaginatorTrait
     */
    private $trait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Paginator
     */
    private $paginator;

    public function setUp()
    {
        $this->trait = $this->getMockForTrait(PaginatorTrait::class);
        $this->paginator = $this->createMock(Paginator::class);
    }

    public function testSetPaginator()
    {
        $this->assertFalse($this->trait->hasPaginator());
        $this->assertEquals($this->trait, $this->trait->setPaginator($this->paginator));
        $this->assertTrue($this->trait->hasPaginator());
        $this->assertEquals($this->paginator, $this->trait->getPaginator());
    }

    public function testGetPaginatorWasNotSetException()
    {
        $this->expectException(PaginationException::class);
        $this->trait->getPaginator();
    }
}