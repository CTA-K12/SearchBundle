<?php

namespace CTA\SearchBundle\Tests\Annotation;

use PHPUnit\Framework\TestCase;
use CTA\SearchBundle\Annotation\Searchable;

class SearchableTest extends TestCase
{
    public function testGetFields()
    {
        $searchable = new Searchable(['fields' => ['one', 'two']]);
        $this->assertCount(2, $searchable->getFields());
        $this->assertContains('one', $searchable->getFields());
        $this->assertContains('two', $searchable->getFields());

        $searchable = new Searchable([]);
        $this->assertNull($searchable->getFields());
    }

    public function testIsEntity()
    {
        $searchable = new Searchable([]);
        $this->assertFalse($searchable->isEntity());

        $searchable = new Searchable(['fields' => ['taco']]);
        $this->assertTrue($searchable->isEntity());

        $searchable = new Searchable(['fields' => []]);
        $this->assertFalse($searchable->isEntity());
    }

    public function testIsField()
    {
        $searchable = new Searchable([]);
        $this->assertTrue($searchable->isField());

        $searchable = new Searchable(['fields' => ['taco']]);
        $this->assertFalse($searchable->isField());

        $searchable = new Searchable(['fields' => []]);
        $this->assertTrue($searchable->isField());
    }
}
