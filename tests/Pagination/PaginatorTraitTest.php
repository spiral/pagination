<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Pagination\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
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
    const PAGINATOR_LIMIT = 10;
    const PAGINATOR_COUNT = 15;
    const PAGINATOR_PARAMETER = 'test';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PaginatorTrait
     */
    private $trait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Paginator
     */
    private $paginator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $this->trait = $this->getMockForTrait(PaginatorTrait::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->paginator = $this->createMock(Paginator::class);
    }

    public function testSetPaginator()
    {
        ContainerScope::runScope($this->container, function () {
            $this->assertFalse($this->trait->hasPaginator());
            $this->assertEquals($this->trait, $this->trait->setPaginator($this->paginator));
            $this->assertTrue($this->trait->hasPaginator());
            $this->assertEquals($this->paginator, $this->trait->getPaginator());
        });
    }

    public function testGetPaginatorWasNotSetException()
    {
        ContainerScope::runScope($this->container, function () {
            $this->expectException(PaginationException::class);
            $this->trait->getPaginator();
        });
    }

    public function testPaginate()
    {
        ContainerScope::runScope($this->container, function () {
            $paginators = $this->createMock(PaginatorsInterface::class);
            $paginators->method('createPaginator')
                ->with(static::PAGINATOR_PARAMETER, static::PAGINATOR_LIMIT)
                ->willReturn($this->paginator);

            $this->container->method('has')->with(PaginatorsInterface::class)->willReturn(true);
            $this->container->method('get')->with(PaginatorsInterface::class)->willReturn($paginators);

            $this->assertEquals(
                $this->trait,
                $this->trait->setPaginator(static::PAGINATOR_LIMIT, static::PAGINATOR_PARAMETER)
            );
        });
    }

    public function testPaginateScopeExceptionNoContainer()
    {
        ContainerScope::runScope($this->container, function () {
            $this->expectException(ScopeException::class);
            $this->trait->setPaginator();
        });
    }
}