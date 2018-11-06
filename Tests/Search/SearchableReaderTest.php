<?php

namespace CTA\SearchBundle\Tests\Search;

use PHPUnit\Framework\TestCase;
use CTA\SearchBundle\Tests\App\SearchTestKernel;
use CTA\SearchBundle\Tests\App\Entity\Foo;
use CTA\SearchBundle\Tests\App\Entity\Bar;
use CTA\SearchBundle\Search\SearchableReader;
use CTA\SearchBundle\Search\SearchableAttributes;

class SearchableReaderTest extends TestCase
{
    public function testRead()
    {
        // Read the mock entity
        $kernel = new SearchTestKernel('test', true);
        $kernel->boot();

        $annotationsReader = $kernel->getContainer()->get('test.service_container')->get('annotations.reader');
        $reader = new SearchableReader($annotationsReader);

        // Read Foo
        $attributes = $reader->read(Foo::class);
        $this->assertInstanceOf(SearchableAttributes::class, $attributes);

        // Check that the fields are where we think they should be
        $this->assertCount(2, $attributes->getFilterableAttributes());
        $this->assertContains('id', $attributes->getFilterableAttributes());
        $this->assertContains('color', $attributes->getFilterableAttributes());

        $this->assertCount(3, $attributes->getSearchableAttributes());
        $this->assertContains('name', $attributes->getSearchableAttributes());
        $this->assertContains('description', $attributes->getSearchableAttributes());
        $this->assertContains('color', $attributes->getSearchableAttributes());

        $this->assertCount(3, $attributes->getSortableAttributes());
        $this->assertContains('name', $attributes->getSortableAttributes());
        $this->assertContains('color', $attributes->getSortableAttributes());
        $this->assertContains('rank', $attributes->getSortableAttributes());

        // Read Bar
        $attributes = $reader->read(Bar::class);
        $this->assertInstanceOf(SearchableAttributes::class, $attributes);

        $this->assertCount(1, $attributes->getFilterableAttributes());
        $this->assertContains('id', $attributes->getFilterableAttributes());

        $this->assertCount(2, $attributes->getSearchableAttributes());
        $this->assertContains('name', $attributes->getSearchableAttributes());
        $this->assertArrayHasKey('foo', $attributes->getSearchableAttributes());
        $this->assertCount(3, $attributes->getSearchableAttributes()['foo']);
        $this->assertContains('name', $attributes->getSearchableAttributes()['foo']);
        $this->assertContains('description', $attributes->getSearchableAttributes()['foo']);
        $this->assertContains('color', $attributes->getSearchableAttributes()['foo']);

        $this->assertCount(3, $attributes->getSortableAttributes());
        $this->assertContains('id', $attributes->getSortableAttributes());
        $this->assertContains('name', $attributes->getSortableAttributes());
        $this->assertArrayHasKey('foo', $attributes->getSortableAttributes());
        $this->assertCount(2, $attributes->getSortableAttributes()['foo']);
        $this->assertContains('color', $attributes->getSortableAttributes()['foo']);
        $this->assertContains('rank', $attributes->getSortableAttributes()['foo']);
    }
}
