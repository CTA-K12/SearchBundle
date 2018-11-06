<?php

namespace CTA\SearchBundle\Tests\Search;

use PHPUnit\Framework\TestCase;
use CTA\SearchBundle\Search\SearchResult;
use CTA\SearchBundle\Search\Search;
use CTA\SearchBundle\Search\SearchableAttributes;

class SearchResultTest extends TestCase
{
    public function testGetResults()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $results = new SearchResult(['one', 'three'], 2, new Search($attributes));

        $this->assertCount(2, $results->getResults());
        $this->assertContains('one', $results->getResults());
        $this->assertContains('three', $results->getResults());

        $results = new SearchResult([], 0, new Search($attributes));

        $this->assertEmpty($results->getResults());
    }

    public function testGetTotal()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $results = new SearchResult(['one', 'three'], 2, new Search($attributes));

        $this->assertEquals(2, $results->getTotal());

        $results = new SearchResult([], 0, new Search($attributes));

        $this->assertEquals(0, $results->getTotal());
    }

    public function testGetSearch()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $results = new SearchResult(['one', 'three'], 2, new Search($attributes, 77));

        $this->assertInstanceOf(Search::class, $results->getSearch());
        $this->assertEquals(77, $results->getSearch()->getLimit());

        $results = new SearchResult([], 0, new Search($attributes, 88));

        $this->assertInstanceOf(Search::class, $results->getSearch());
        $this->assertEquals(88, $results->getSearch()->getLimit());
    }
}
