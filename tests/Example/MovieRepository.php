<?php

namespace Webgriffe\InMemoryRepository\Tests\Example;

use Webgriffe\InMemoryRepository\ObjectRepository;

/**
 * @extends ObjectRepository<array-key, Movie>
 */
final class MovieRepository extends ObjectRepository
{
    public function getClassName(): string
    {
        return Movie::class;
    }

    protected function getIdProperty(): string
    {
        return 'id';
    }
}
