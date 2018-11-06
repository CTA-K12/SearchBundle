<?php

namespace CTA\SearchBundle\Tests\Annotation;

use PHPUnit\Framework\TestCase;
use CTA\SearchBundle\Annotation\Sortable;

class SortableTest extends TestCase
{
    public function testGetFields()
    {
        $searchable = new Sortable(['fields' => ['one', 'two']]);
        $this->assertCount(2, $searchable->getFields());
        $this->assertContains('one', $searchable->getFields());
        $this->assertContains('two', $searchable->getFields());

        $searchable = new Sortable([]);
        $this->assertNull($searchable->getFields());
    }

    public function testIsEntity()
    {
        $searchable = new Sortable([]);
        $this->assertFalse($searchable->isEntity());

        $searchable = new Sortable(['fields' => ['taco']]);
        $this->assertTrue($searchable->isEntity());

        $searchable = new Sortable(['fields' => []]);
        $this->assertFalse($searchable->isEntity());
    }

    public function testIsField()
    {
        $searchable = new Sortable([]);
        $this->assertTrue($searchable->isField());

        $searchable = new Sortable(['fields' => ['taco']]);
        $this->assertFalse($searchable->isField());

        $searchable = new Sortable(['fields' => []]);
        $this->assertTrue($searchable->isField());
    }
}
