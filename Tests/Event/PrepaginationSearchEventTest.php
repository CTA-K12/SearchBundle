<?php

namespace CTA\SearchBundle\Tests\Event;

use PHPUnit\Framework\TestCase;

use CTA\SearchBundle\Event\PrepaginationSearchEvent;
use CTA\SearchBundle\Search\Search;
use CTA\SearchBundle\Search\SearchableAttributes;
use Doctrine\ORM\QueryBuilder;

class PrepaginationSearchEventTest extends TestCase
{
    public function testGetAlias()
    {
        $search = $this->createMock(Search::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $psEvent = new PrepaginationSearchEvent('TEST', $search, $queryBuilder);
        $this->assertEquals('TEST', $psEvent->getAlias());
    }

    public function testGetSearch()
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $attributes = $this->createMock(SearchableAttributes::class);
        $search = new Search($attributes, 1234);
        $psEvent = new PrepaginationSearchEvent('TEST', $search, $queryBuilder);

        $returnSearch = $psEvent->getSearch();
        $this->assertInstanceOf(Search::class, $returnSearch);
        $this->assertEquals(1234, $returnSearch->getLimit());
    }

    public function testGetQueryBuilder()
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $search = $this->createMock(Search::class);
        $psEvent = new PrepaginationSearchEvent('TEST', $search, $queryBuilder);

        $this->assertInstanceOf(QueryBuilder::class, $psEvent->getQueryBuilder());
    }
}
