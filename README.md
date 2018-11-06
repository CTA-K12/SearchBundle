CTA Search Bundle
=================
Provides a quick way to add filtering, sorting, and text searching to an entity.  Currently, the search setup is far from being efficient or high performance, but it allows us to quickly build out searching tools for our government business requirements where we need search for a large amount of entities that don't have too many records.  Hopefully though this can be used a starting point for building something better in the future.

Requirements
------------
This bundle is built exclusively for use in Symfony 4 and PHP 7, and assumes that your entities are managed by Doctrine ORM.

Getting Started
---------------
To install the bundle, first add it to your project through composer.
```bash
$ composer require cta/search-bundle
```
Then make sure that the search bundle is added to `config/bundles.php`
```PHP
<?php

return [
    // ...
    CTA\SearchBundle\CTASearchBundle::class => ['all' => true],
];
```

### Making Entities Searchable
The bundle adds three types annotations that you can mark your entities with, Searchable, Filterable, and Sortable.  Searchable marks which fields you can run a full text search on using case insensitive like operations, Sortable marks which fields can be sorted on during a search, and Filterable marks which fields can be directly filtered where only results that exactly match the filter are returned.  Joined entities can also have their properties marked as Searchable or Sortable as well by adding fields array to the annotation.

The bundle also includes a repository class that has the search
method built in.  To use this repository you must either set the entity to have it as its repository
class or you need to have the entity's repository class extend the `CTA\SearchBundle\Repository\SearchableRepository` class.

```PHP
<?php

use CTA\SearchBundle\Annotation as Search; // Import the search bundle annotations
// ...

/**
 * @ORM/Entity(repositoryClass="CTA\SearchBundle\Repository\SearchableRepository")
 */
class MyEntity
{
  /**
   * ...
   * This property can be filtered or sorted
   *
   * @Search\Filterable
   * @Search\Sortable
   */
  private $id;

  /**
   * ...
   * This property can be searched on or sorted
   *
   * @Search\Searchable
   * @Search\Sortable
   */
  private $name;

  /**
   * ...
   * This relation can have its name and description field searched
   * and its name and rank field sorted
   *
   * @Search\Searchable(fields={"name", "description"})
   * @Search\Sortable(fields={"name", "rank"})
   */
  private $myOtherEntity;

  // ...
}
```

### Running a Search
Once the annotations have been added to an entity, and its repository has been set to either `CTA\SearchBundle\Repository\SearchableRepository` or a repository that extends it.  You can use the
`CTA\SearchBundle\Search\SearchableManager` to create a search object to send to the repository's search method.  The search requires the fully qualified class name of the entity to obtain its searchable attributes information, along with the set of search parameters which are as follows:

1. FQCN of the entity, e.g. `App\Entity\MyEntity`
2. The number of results to return (page size)
3. First result to return (offset)
4. Filters for the search in the form of an array where the key is the property and the value is the value to filter on.  For example `['id' => 3]` will only get entities that have an id of 3.  Default is `[]`.
5. Search text string, defaults to `''`.
6. Sorts for the search, again in the form of an array where the key is the property and the value is either `ASC` or `DESC` for the direction of the sort.  For example `['name' => 'ASC', 'myOtherEntity.rank' => 'DESC']` would sort by name in ascending order first then sort by the relation's rank property in descending order second.  Defaults to `[]`.

Example:
```PHP
<?php

use CTA\SearchBundle\Search\SearchableManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MyEntity;

class Searcher
{
  private $searchableManager;

  public function __construct(SearchableManager $searchableManager, EntityManagerInterface $entityManager)
  {
    $this->searchableManager = $searchableManager;
    $this->entityManager = $entityManager;
  }

  public function searchStuff()
  {
    // Lets search for all the entities using the text search "this is a test" where page size is 20
    // and we are trying to get the third page of results and sorting on myOtherEntity rank
    $search = $searchableManager->createSearch(MyEntity::class, 20, 40, [], 'this is a test', ['myOtherEntity' => 'ASC']);
    $results = $entityManager->getRepository(MyEntity::class)->search($search);

    // Get the total number of unpaginated results
    $totalCount = $results->getTotalCount();
    // Get the entities
    $entities = $results->getResults();
  }
}
```

### Manipulating the Search Query
The searchable repository's search method will dispatch an event before it begins to paginate the results in order to allow manipulation of the base search query.  The event is the `CTA\SearchBundle\Event\PrepaginationSearchEvent` and contains the querybuilder that is being built by the search method.  To dispatch the event during a search add an event dispatcher as the second argument to the search method `$entityManager->getRepository(MyEntity::class)->search($search, $symfonyEventDispatcher)` where the `$symfonyEventDispatcher` is a class that follows Symfony's `EventDispatcherInterface`.  The name of the event will be based on alias from your entity's name where camel case is replaced by snake case (e.g. MyEntity will be 'my_entity' and Foo would be 'foo') plus the string `_search_prepagination` added on (again e.g. MyEntity would dispatch the event under `my_entity_search_prepagination`).

Example event subscriber:
```PHP
<?php

namespace App\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use CTA\SearchBundle\Event\PrepaginationSearchEvent;

class MySubscriber implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return [
      'my_entity_' . PrepaginationSearchEvent::NAME => 'onSearchPrepagination',
    ];
  }

  public function onSearchPrepagination(PrepaginationSearchEvent $event)
  {
    // Get the alias being used by the query builder for the root entity
    $alias = $event->getAlias();

    // Get the query builder from the search
    $queryBuilder = $event->getQueryBuilder();

    // Manipulate
    $queryBuilder->andWhere($alias . '.id > 100');
  }
}
```

Testing
-------

To test the bundle, first add the development dependencies:
```Bash
$ composer install --dev
```

Then run PHPUnit:
```Bash
$ ./vendor/bin/phpunit
```
