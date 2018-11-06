<?php

namespace CTA\SearchBundle\Tests\Search;

use PHPUnit\Framework\TestCase;
use CTA\SearchBundle\Search\SearchableAttributes;
use CTA\SearchBundle\Annotation\Searchable;
use CTA\SearchBundle\Annotation\Sortable;
use CTA\SearchBundle\Annotation\Filterable;

class SearchableAttributesTest extends TestCase
{
    public function testAddGetSearchableAttributes()
    {
        $attributes = new SearchableAttributes();
        $this->assertEmpty($attributes->getSearchableAttributes());

        $property = new \ReflectionProperty(SearchableAttributes::class, 'filterable');
        $searchableAnnotation = new Searchable([]);

        $attributes->addSearchableAttribute($searchableAnnotation, $property);
        $searchable = $attributes->getSearchableAttributes();

        $this->assertCount(1, $searchable);
        $this->assertEquals('filterable', $searchable[0]);

        $property = new \ReflectionProperty(SearchableAttributes::class, 'sortable');
        $searchableAnnotation = new Searchable(['fields' => ['foo', 'bar']]);
        $attributes->addSearchableAttribute($searchableAnnotation, $property);
        $searchable = $attributes->getSearchableAttributes();

        $this->assertCount(2, $searchable);
        $this->assertEquals('filterable', $searchable[0]);
        $this->assertArrayHasKey('sortable', $searchable);
        $this->assertCount(2, $searchable['sortable']);
        $this->assertContains('foo', $searchable['sortable']);
        $this->assertContains('bar', $searchable['sortable']);
    }

    public function testAddGetSortableAttributes()
    {
        $attributes = new SearchableAttributes();
        $this->assertEmpty($attributes->getSortableAttributes());

        $property = new \ReflectionProperty(SearchableAttributes::class, 'filterable');
        $sortableAnnotation = new Sortable([]);

        $attributes->addSortableAttribute($sortableAnnotation, $property);
        $sortable = $attributes->getSortableAttributes();

        $this->assertCount(1, $sortable);
        $this->assertEquals('filterable', $sortable[0]);

        $property = new \ReflectionProperty(SearchableAttributes::class, 'searchable');
        $sortableAnnotation = new Sortable(['fields' => ['foo', 'bar']]);
        $attributes->addSortableAttribute($sortableAnnotation, $property);
        $sortable = $attributes->getSortableAttributes();

        $this->assertCount(2, $sortable);
        $this->assertEquals('filterable', $sortable[0]);
        $this->assertArrayHasKey('searchable', $sortable);
        $this->assertCount(2, $sortable['searchable']);
        $this->assertContains('foo', $sortable['searchable']);
        $this->assertContains('bar', $sortable['searchable']);
    }

    public function testAddGetFilterableAttributes()
    {
        $attributes = new SearchableAttributes();
        $this->assertEmpty($attributes->getFilterableAttributes());

        $property = new \ReflectionProperty(SearchableAttributes::class, 'sortable');
        $filterableAnnotation = new Filterable([]);

        $attributes->addFilterableAttribute($filterableAnnotation, $property);
        $filterable = $attributes->getFilterableAttributes();

        $this->assertCount(1, $filterable);
        $this->assertContains('sortable', $filterable);

        $property = new \ReflectionProperty(SearchableAttributes::class, 'searchable');
        $filterableAnnotation = new Filterable([]);

        $attributes->addFilterableAttribute($filterableAnnotation, $property);
        $filterable = $attributes->getFilterableAttributes();

        $this->assertCount(2, $filterable);
        $this->assertContains('sortable', $filterable);
        $this->assertContains('searchable', $filterable);
    }
}
