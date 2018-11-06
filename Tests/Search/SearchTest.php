<?php

namespace CTA\SearchBundle\Tests\Search;

use PHPUnit\Framework\TestCase;
use CTA\SearchBundle\Search\Search;
use CTA\SearchBundle\Search\SearchableAttributes;

class SearchTest extends TestCase
{
    public function testGetFilters()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $attributes->method('getFilterableAttributes')
                   ->willReturn(['taco', 'unicorn']);

        $search = new Search($attributes, 15, 0, ['taco' => 'tasty', 'unicorn' => 'magic']);
        $filters = $search->getFilters();

        $this->assertCount(2, $filters);
        $this->assertArrayHasKey('taco', $filters);
        $this->assertArrayhasKey('unicorn', $filters);
        $this->assertEquals('tasty', $filters['taco']);
        $this->assertEquals('magic', $filters['unicorn']);

        $search = new Search($attributes, 15, 0, ['unicorn' => 'pink', 'state' => 'liquid']);
        $filters = $search->getFilters();

        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('unicorn', $filters);
        $this->assertArrayNotHasKey('state', $filters);
        $this->assertArrayNotHasKey('taco', $filters);
        $this->assertEquals('pink', $filters['unicorn']);

        $attributes = $this->createMock(SearchableAttributes::class);
        $attributes->method('getFilterableAttributes')
                   ->willReturn([]);

        $search = new Search($attributes);
        $filters = $search->getFilters();
        $this->assertEmpty($filters);
    }

    public function testAddFilter()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $attributes->method('getFilterableAttributes')
                   ->willReturn(['taco', 'unicorn']);

        $search = new Search($attributes);
        $this->assertEmpty($search->getFilters());

        $search->addFilter('taco', 'yummy');
        $filters = $search->getFilters();
        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('taco', $filters);
        $this->assertEquals('yummy', $filters['taco']);

        $search->addFilter('unicorn', 'grumpy');
        $filters = $search->getFilters();
        $this->assertCount(2, $filters);
        $this->assertArrayHasKey('taco', $filters);
        $this->assertArrayHasKey('unicorn', $filters);
        $this->assertEquals('yummy', $filters['taco']);
        $this->assertEquals('grumpy', $filters['unicorn']);

        $search->addFilter('taco', 'chorizo');
        $filters = $search->getFilters();
        $this->assertCount(2, $filters);
        $this->assertArrayHasKey('taco', $filters);
        $this->assertArrayHasKey('unicorn', $filters);
        $this->assertEquals('chorizo', $filters['taco']);
        $this->assertEquals('grumpy', $filters['unicorn']);

        $search->addFilter('superpower', 'invisibility');
        $filters = $search->getFilters();
        $this->assertCount(2, $filters);
        $this->assertArrayHasKey('taco', $filters);
        $this->assertArrayHasKey('unicorn', $filters);
        $this->assertArrayNotHasKey('superpower', $filters);
    }

    public function testHasSearchTerms()
    {
        $attributes = $this->createMock(SearchableAttributes::class);

        $search = new Search($attributes, 15, 0, [], 'search');
        $this->assertTrue($search->hasSearchTerms());

        $search = new Search($attributes, 15, 0, [], '');
        $this->assertFalse($search->hasSearchTerms());

        $search = new Search($attributes, 15, 0, [], '            ');
        $this->assertFalse($search->hasSearchTerms());

        $search = new Search($attributes, 15, 0, [], '  HELLO WORLD   ');
        $this->assertTrue($search->hasSearchTerms());
    }

    public function testGetSearchTerms()
    {
        $attributes = $this->createMock(SearchableAttributes::class);

        $search = new Search($attributes, 15, 0, [], 'search');
        $terms = $search->getSearchTerms();
        $this->assertCount(1, $terms);
        $this->assertContains('search', $terms);

        $search = new Search($attributes, 15, 0, [], '     ');
        $terms = $search->getSearchTerms();
        $this->assertEmpty($terms);

        $search = new Search($attributes, 15, 0, [], 'Howdy World');
        $terms = $search->getSearchTerms();
        $this->assertCount(2, $terms);
        $this->assertContains('howdy', $terms);
        $this->assertContains('world', $terms);

        $search = new Search($attributes, 15, 0, [], '   writing TESTS iS bORINg  ');
        $terms = $search->getSearchTerms();
        $this->assertCount(4, $terms);
        $this->assertContains('writing', $terms);
        $this->assertContains('tests', $terms);
        $this->assertContains('is', $terms);
        $this->assertContains('boring', $terms);
    }

    public function testSetSearch()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $search = new Search($attributes);

        $this->assertFalse($search->hasSearchTerms());
        $this->assertEmpty($search->getSearchTerms());

        $search->setSearch('I Hate tURTLeS');
        $this->assertTrue($search->hasSearchTerms());
        $terms = $search->getSearchTerms();
        $this->assertCount(3, $terms);
        $this->assertContains('i', $terms);
        $this->assertContains('hate', $terms);
        $this->assertContains('turtles', $terms);

        $search->setSearch('                ');
        $this->assertFalse($search->hasSearchTerms());
        $this->assertEmpty($search->getSearchTerms());

        $search->setSearch('okay');
        $this->assertTrue($search->hasSearchTerms());
        $terms = $search->getSearchTerms();
        $this->assertCount(1, $terms);
        $this->assertContains('okay', $terms);
    }

    public function testGetSorts()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $attributes->method('getSortableAttributes')
                   ->willReturn(['foo', 'bar', 'superpower' => ['length', 'strength']]);

        $search = new Search($attributes, 15, 0, [], '', []);
        $this->assertEmpty($search->getSorts());

        $search = new Search($attributes, 15, 0, [], '', ['foo' => 'ASC']);
        $sorts = $search->getSorts();
        $this->assertCount(1, $sorts);
        $this->assertArrayHasKey('property', $sorts[0]);
        $this->assertEquals('foo', $sorts[0]['property']);
        $this->assertArrayHasKey('direction', $sorts[0]);
        $this->assertEquals('ASC', $sorts[0]['direction']);

        $search = new Search($attributes, 15, 0, [], '', ['bar' => 'DESC', 'foo' => 'ASC', 'color' => 'ASC']);
        $sorts = $search->getSorts();
        $this->assertCount(2, $sorts);
        $this->assertArrayHasKey('property', $sorts[0]);
        $this->assertEquals('bar', $sorts[0]['property']);
        $this->assertArrayHasKey('direction', $sorts[0]);
        $this->assertEquals('DESC', $sorts[0]['direction']);
        $this->assertArrayHasKey('property', $sorts[1]);
        $this->assertEquals('foo', $sorts[1]['property']);
        $this->assertArrayHasKey('direction', $sorts[1]);
        $this->assertEquals('ASC', $sorts[1]['direction']);

        $search = new Search($attributes, 15, 0, [], '', ['superpower.length' => 'ASC', 'superpower.strength' => 'DESC', 'superpower.name' => 'ASC']);
        $sorts = $search->getSorts();
        $this->assertCount(2, $sorts);
        $this->assertArrayHasKey('property', $sorts[0]);
        $this->assertCount(2, $sorts[0]['property']);
        $this->assertEquals('superpower', $sorts[0]['property'][0]);
        $this->assertEquals('length', $sorts[0]['property'][1]);
        $this->assertArrayHasKey('direction', $sorts[0]);
        $this->assertEquals('ASC', $sorts[0]['direction']);
        $this->assertEquals('superpower', $sorts[1]['property'][0]);
        $this->assertEquals('strength', $sorts[1]['property'][1]);
        $this->assertArrayHasKey('direction', $sorts[0]);
        $this->assertEquals('DESC', $sorts[1]['direction']);
    }

    public function testAddSort()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $attributes->method('getSortableAttributes')
                   ->willReturn(['foo', 'bar', 'superpower' => ['length', 'strength']]);

        $search = new Search($attributes, 15, 0, [], '', []);
        $this->assertEmpty($search->getSorts());

        $search->addSort('foo', 'ASC');
        $sorts = $search->getSorts();
        $this->assertCount(1, $sorts);
        $this->assertArrayHasKey('property', $sorts[0]);
        $this->assertEquals('foo', $sorts[0]['property']);
        $this->assertArrayHasKey('direction', $sorts[0]);
        $this->assertEquals('ASC', $sorts[0]['direction']);

        $search->addSort('color', 'DESC');
        $sorts = $search->getSorts();
        $this->assertCount(1, $sorts);
        $this->assertArrayHasKey('property', $sorts[0]);
        $this->assertEquals('foo', $sorts[0]['property']);
        $this->assertArrayHasKey('direction', $sorts[0]);
        $this->assertEquals('ASC', $sorts[0]['direction']);

        $search->addSort('bar', 'DESC');
        $sorts = $search->getSorts();
        $this->assertCount(2, $sorts);
        $this->assertArrayHasKey('property', $sorts[0]);
        $this->assertEquals('foo', $sorts[0]['property']);
        $this->assertArrayHasKey('direction', $sorts[0]);
        $this->assertEquals('ASC', $sorts[0]['direction']);
        $this->assertArrayHasKey('property', $sorts[1]);
        $this->assertEquals('bar', $sorts[1]['property']);
        $this->assertArrayHasKey('direction', $sorts[1]);
        $this->assertEquals('DESC', $sorts[1]['direction']);
    }

    /**
     * @expectedException \Exception
     */
    public function testAddSortDirectionException()
    {
        $attributes = $this->createMock(SearchableAttributes::class);
        $attributes->method('getSortableAttributes')
                   ->willReturn(['foo']);

        $search = new Search($attributes, 15, 0, [], '', []);
        $this->assertEmpty($search->getSorts());

        $search->addSort('foo', 'turtles');
    }


    public function testGetLimit()
    {
        $attributes = $this->createMock(SearchableAttributes::class);

        $search = new Search($attributes);
        $this->assertEquals(15, $search->getLimit());

        $search = new Search($attributes, 25);
        $this->assertEquals(25, $search->getLimit());
    }


    public function testGetOffset()
    {
        $attributes = $this->createMock(SearchableAttributes::class);

        $search = new Search($attributes);
        $this->assertEquals(0, $search->getOffset());

        $search = new Search($attributes, 15, 60);
        $this->assertEquals(60, $search->getOffset());
    }


    public function testGetAttributes()
    {
        $attributes = $this->createMock(SearchableAttributes::class);

        $search = new Search($attributes);
        $this->assertInstanceOf(SearchableAttributes::class, $search->getAttributes());
    }
}
