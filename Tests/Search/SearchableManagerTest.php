<?php

namespace CTA\SearchBundle\Tests\Search;

use PHPUnit\Framework\TestCase;

use CTA\SearchBundle\Search\SearchableManager;
use CTA\SearchBundle\Search\SearchableReader;
use CTA\SearchBundle\Search\SearchableAttributes;
use CTA\SearchBundle\Search\Search;
use CTA\SearchBundle\Annotation\Searchable;

class SearchableManagerTest extends TestCase
{
    public function testGetAttributesForEntity()
    {
        $oneAttributes = new SearchableAttributes();
        $oneAttributes->addSearchableAttribute(new Searchable([]), new \ReflectionProperty(SearchableAttributes::class, 'searchable'));

        $twoAttributes = new SearchableAttributes();
        $twoAttributes->addSearchableAttribute(new Searchable([]), new \ReflectionProperty(Searchable::class, 'fields'));

        $reader = $this->createMock(SearchableReader::class);
        $reader->expects($this->at(0))
               ->method('read')
               ->with($this->equalTo('CTA\\SearchBundle\\Search\\SearchableAttributes'))
               ->willReturn($oneAttributes);
       $reader->expects($this->at(1))
              ->method('read')
              ->with($this->equalTo('CTA\\SearchBundle\\Annotation\\Searchable'))
              ->willReturn($twoAttributes);

        $manager = new SearchableManager($reader);

        $this->assertEquals($oneAttributes, $manager->getAttributesForEntity('CTA\\SearchBundle\\Search\\SearchableAttributes'));
        $this->assertEquals($twoAttributes, $manager->getAttributesForEntity('CTA\\SearchBundle\\Annotation\\Searchable'));
        $this->assertEquals($oneAttributes, $manager->getAttributesForEntity('CTA\\SearchBundle\\Search\\SearchableAttributes'));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetAttributesForEntityNoEntityException()
    {
        $reader = $this->createMock(SearchableReader::class);

        $manager = new SearchableManager($reader);
        $manager->getAttributesForEntity('Meow\\MeowBundle\\MeowMeow');
    }

    public function testCreateSearch()
    {
        $attributes = new SearchableAttributes();
        $reader = $this->createMock(SearchableReader::class);
        $reader->method('read')
               ->with($this->equalTo(SearchableAttributes::class))
               ->willReturn($attributes);

        $manager = new SearchableManager($reader);
        $search = $manager->createSearch(SearchableAttributes::class);

        $this->assertInstanceOf(Search::class, $search);
        $this->assertEquals(15, $search->getLimit());
        $this->assertEquals(0, $search->getOffset());
        $this->assertEmpty($search->getSearchTerms());
        $this->assertEmpty($search->getSorts());
        $this->assertEmpty($search->getFilters());

        $search = $manager->createSearch(SearchableAttributes::class, 20, 40);

        $this->assertInstanceOf(Search::class, $search);
        $this->assertEquals(20, $search->getLimit());
        $this->assertEquals(40, $search->getOffset());
    }
}
