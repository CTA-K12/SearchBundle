<?php

namespace CTA\SearchBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use CTA\SearchBundle\Search\Search;
use CTA\SearchBundle\Search\SearchResult;
use CTA\SearchBundle\Search\SearchableManager;
use CTA\SearchBundle\Search\SearchableReader;
use CTA\SearchBUndle\Repository\SearchableRepository;
use CTA\SearchBundle\Tests\App\Entity\Foo;
use CTA\SearchBundle\Tests\App\Entity\Bar;
use CTA\SearchBundle\Tests\App\SearchTestKernel;
use Doctrine\ORM\Tools\SchemaTool;

class SearchableRepositoryTest extends TestCase
{
    public function testGetAliasForSearchQuery()
    {
        $kernel = new SearchTestKernel('test', true);
        $kernel->boot();

        $doctrine = $kernel->getContainer()->get('test.service_container')->get('doctrine');

        $this->assertInstanceOf(SearchableRepository::class, $doctrine->getEntityManager()->getRepository(Foo::class));
        $this->assertEquals('foo', $doctrine->getEntityManager()->getRepository(Foo::class)->getAliasForSearchQuery());

        $this->assertInstanceOf(SearchableRepository::class, $doctrine->getEntityManager()->getRepository(Bar::class));
        $this->assertEquals('bar', $doctrine->getEntityManager()->getRepository(Bar::class)->getAliasForSearchQuery());
    }

    public function testSearch()
    {
        $kernel = new SearchTestKernel('test', true);
        $kernel->boot();

        $entityManager = $kernel->getContainer()->get('test.service_container')->get('doctrine')->getEntityManager();
        $annotationsReader = $kernel->getContainer()->get('test.service_container')->get('annotations.reader');
        $reader = new SearchableReader($annotationsReader);
        $searchable = new SearchableManager($reader);

        // Clean out any old version of the test db
        exec('rm ' . $entityManager->getConnection()->getParams()['path']);

        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metadata);

        // Create lazy fixtures
        $foo1 = new Foo();
        $foo1->name = 'Apple';
        $foo1->description = 'A tree fruit sometimes green too';
        $foo1->color = 'Red';
        $foo1->rank = 1;
        $entityManager->persist($foo1);

        $foo2 = new Foo();
        $foo2->name = 'Orange';
        $foo2->description = 'A citric tree fruit';
        $foo2->color = 'Orange';
        $foo2->rank = 2;
        $entityManager->persist($foo2);

        $foo3 = new Foo();
        $foo3->name = 'Grape';
        $foo3->description = 'A vine fruit';
        $foo3->color = 'Purple';
        $foo3->rank = 3;
        $entityManager->persist($foo3);

        $foo4 = new Foo();
        $foo4->name = 'Spinach';
        $foo4->description = 'Leafy vegetable';
        $foo4->color = 'Green';
        $foo4->rank = 4;
        $entityManager->persist($foo4);

        $bar1 = new Bar();
        $bar1->name = 'This is a thing';
        $bar1->foo = $foo1;
        $entityManager->persist($bar1);

        $bar2 = new Bar();
        $bar2->name = 'La lala la';
        $bar2->foo = $foo4;
        $entityManager->persist($bar2);

        $entityManager->flush();

        // Search Foo for fruit sorted by name
        $search = $searchable->createSearch(Foo::class, 15, 0, [], 'FrUiT', ['name' => 'ASC']);
        $results = $entityManager->getRepository(Foo::class)->search($search);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(3, $results->getTotal());
        $this->assertCount(3, $results->getResults());

        // Assert Apple -> Grape -> Orange for the sorting and the search
        $output = $results->getResults();
        $this->assertEquals('Apple', $output[0]->name);
        $this->assertEquals('Grape', $output[1]->name);
        $this->assertEquals('Orange', $output[2]->name);

        // Search Foo for spi table and check that the event dispatcher is triggered
        $dispatch = $this->createMock(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $dispatch->expects($this->once())->method('dispatch');
        $search = $searchable->createSearch(Foo::class, 15, 0, [], 'sPI TABle');
        $results = $entityManager->getRepository(Foo::class)->search($search, $dispatch);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(1, $results->getTotal());
        $this->assertCount(1, $results->getResults());
        $this->assertEquals('Spinach', $results->getResults()[0]->name);

        // Filter out by color
        $search = $searchable->createSearch(Foo::class, 15, 0, ['color' => 'Orange'], '');
        $results = $entityManager->getRepository(Foo::class)->search($search);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(1, $results->getTotal());
        $this->assertCount(1, $results->getResults());
        $this->assertEquals('Orange', $results->getResults()[0]->name);

        // Inverse sort by rank
        $search = $searchable->createSearch(Foo::class, 15, 0, [], '', ['rank' => 'DESC']);
        $results = $entityManager->getRepository(Foo::class)->search($search);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(4, $results->getTotal());
        $this->assertCount(4, $results->getResults());

        $output = $results->getResults();
        $this->assertEquals('Spinach', $output[0]->name);
        $this->assertEquals('Grape', $output[1]->name);
        $this->assertEquals('Orange', $output[2]->name);
        $this->assertEquals('Apple', $output[3]->name);

        // Again but limit the size to 3
        $search = $searchable->createSearch(Foo::class, 3, 0, [], '', ['rank' => 'DESC']);
        $results = $entityManager->getRepository(Foo::class)->search($search);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(4, $results->getTotal());
        $this->assertCount(3, $results->getResults());

        $output = $results->getResults();
        $this->assertEquals('Spinach', $output[0]->name);
        $this->assertEquals('Grape', $output[1]->name);
        $this->assertEquals('Orange', $output[2]->name);

        // Lets get the next page
        $search = $searchable->createSearch(Foo::class, 3, 3, [], '', ['rank' => 'DESC']);
        $results = $entityManager->getRepository(Foo::class)->search($search);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(4, $results->getTotal());
        $this->assertCount(1, $results->getResults());

        $output = $results->getResults();
        $this->assertEquals('Apple', $output[0]->name);

        // Search on Bar for green sorted by rank
        $search = $searchable->createSearch(Bar::class, 15, 0, [], 'green', ['foo.rank' => 'DESC']);
        $results = $entityManager->getRepository(Bar::class)->search($search);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(2, $results->getTotal());
        $this->assertCount(2, $results->getResults());

        $output = $results->getResults();
        $this->assertEquals('La lala la', $output[0]->name);
        $this->assertEquals('This is a thing', $output[1]->name);

        // One last time for good measure
        $search = $searchable->createSearch(Bar::class, 15, 0, [], 'red');
        $results = $entityManager->getRepository(Bar::class)->search($search);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(1, $results->getTotal());
        $this->assertCount(1, $results->getResults());

        $output = $results->getResults();
        $this->assertEquals('This is a thing', $output[0]->name);

    }
}
