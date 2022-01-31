# Doctrine "in-memory" Repository

This library is an "in-memory" implementation of the `Doctrine\Persistence\ObjectRepository` interface.
It can be used to unit test components which depends on Doctrine repositories without involving a real database.

## Installation

Add it to your "dev" dependencies:

```bash
composer require --dev webgriffe/in-memory-repository
```

## Usage

Let's assume that you're building a movie management application and you have a Doctrine's `MovieRepository` (which implements a `MovieRepositoryInterface`) that's used by your services to fetch movies from database in your application.

Now let's say that to unit test those services which depends on this `MovieRepository` you want to create an "in-memory" implmentation of the `MovieRepositoryInterface`.

With this small library you can easily do this:

```php
<?php

namespace MyMovieApp\Tests\Repository\InMemory;

use MyMovieApp\Model\Movie;
use MyMovieApp\Repository\MovieRepositoryInterface;
use Webgriffe\InMemoryRepository\ObjectRepository;

/**
 * @extends ObjectRepository<array-key,Movie>
 */
final class MovieRepository extends ObjectRepository implements MovieRepositoryInterface
{
}
```

And that's it! You have an "in-memory" implementation of the `MovieRepositoryInterface`.
You can use it in your tests as follows:

```php
$movieRepository = new \MyMovieApp\Tests\Repository\InMemory\MovieRepository();
$movieRepository->objectCollection->add(new Movie('Rambo'));
$movieRepository->objectCollection->add(new Movie('Top Gun'));

$this->assertCount(2, $movieRepository->findAll());
$this->assertEquals(new Movie('Rambo'), $movieRepository->findOneBy(['title' => 'Rambo']));
```

## License

This plugin is under the MIT license. See the complete license in the LICENSE file.

## Credits

Developed by [Webgriffe®](https://www.webgriffe.com).

