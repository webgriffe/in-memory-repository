<?php

namespace Webgriffe\InMemoryRepository\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webgriffe\InMemoryRepository\ObjectCollection;
use Webgriffe\InMemoryRepository\Tests\Example\Movie;
use Webgriffe\InMemoryRepository\Tests\Example\MovieRepository;

final class ObjectRepositoryTest extends TestCase
{
    private MovieRepository $movieRepository;

    protected function setUp(): void
    {
        $this->movieRepository = new MovieRepository();
        $this->movieRepository->objectCollection->add(
            $this->createMovie(
                [
                    'id' => 1,
                    'title' => 'The Lord of the Rings: The Fellowship of the Ring',
                    'year' => 2001,
                    'genre' => 'Fantasy',
                ]
            )
        );
        $this->movieRepository->objectCollection->add(
            $this->createMovie(
                [
                    'id' => 2,
                    'title' => 'Top Gun',
                    'year' => 1986,
                    'genre' => 'Action',
                ]
            )
        );
        $this->movieRepository->objectCollection->add(
            $this->createMovie(
                [
                    'id' => 3,
                    'title' => 'Rambo',
                    'year' => 1982,
                    'genre' => 'Action',
                ]
            )
        );
        $this->movieRepository->objectCollection->add(
            $this->createMovie(
                [
                    'id' => 3,
                    'title' => 'Highlander',
                    'year' => 1986,
                    'genre' => 'Fantasy',
                ]
            )
        );
    }

    /** @test */
    public function it_finds_by_id(): void
    {
        $movie = $this->movieRepository->find(1);

        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals('The Lord of the Rings: The Fellowship of the Ring', $movie->getTitle());
    }

    /** @test */
    public function it_finds_all(): void
    {
        $movies = $this->movieRepository->findAll();

        $this->assertCount(4, $movies);
    }

    /** @test */
    public function it_finds_by_single_criteria(): void
    {
        $movies = $this->movieRepository->findBy(['year' => 1986]);

        $this->assertCount(2, $movies);
    }

    /** @test */
    public function it_finds_by_multiple_criteria(): void
    {
        $movies = $this->movieRepository->findBy(['year' => 1986, 'genre' => 'Action']);

        $this->assertCount(1, $movies);
        $this->assertInstanceOf(Movie::class, $movies->first());
        $this->assertEquals('Top Gun', $movies->first()->getTitle());
    }

    /** @test */
    public function it_finds_one_by_criteria(): void
    {
        $movie = $this->movieRepository->findOneBy(['genre' => 'Action']);

        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals('Action', $movie->getGenre());
    }

    /** @test */
    public function it_sorts_by_one_field_descending(): void
    {
        $movies = $this->movieRepository->findBy([], ['year' => 'DESC']);

        $this->assertCount(4, $movies);
        $this->assertInstanceOf(Movie::class, $movies->first());
        $this->assertInstanceOf(Movie::class, $movies->last());
        $this->assertEquals(2001, $movies->first()->getYear());
        $this->assertEquals(1982, $movies->last()->getYear());
    }

    /** @test */
    public function it_sorts_by_one_field_ascending(): void
    {
        $movies = $this->movieRepository->findBy([], ['year' => 'ASC']);

        $this->assertCount(4, $movies);
        $this->assertInstanceOf(Movie::class, $movies->first());
        $this->assertInstanceOf(Movie::class, $movies->last());
        $this->assertEquals(1982, $movies->first()->getYear());
        $this->assertEquals(2001, $movies->last()->getYear());
    }

    /** @test */
    public function it_sorts_by_two_fields(): void
    {
        $movies = $this->movieRepository->findBy([], ['year' => 'ASC', 'title' => 'ASC']);

        $this->assertCount(4, $movies);
        $movies = $movies->getValues();
        $this->assertEquals('Rambo', $movies[0]->getTitle());
        $this->assertEquals('Highlander', $movies[1]->getTitle());
        $this->assertEquals('Top Gun', $movies[2]->getTitle());
        $this->assertEquals('The Lord of the Rings: The Fellowship of the Ring', $movies[3]->getTitle());
    }

    /** @test */
    public function it_limits_results(): void
    {
        $movies = $this->movieRepository->findBy([], null, 3);

        $this->assertCount(3, $movies);
    }

    /** @test */
    public function it_returns_results_starting_from_offset(): void
    {
        $movies = $this->movieRepository->findBy([], null, null, 2);

        $this->assertCount(2, $movies);
        $this->assertEquals(
            ['Rambo', 'Highlander'],
            $this->mapTitles($movies)
        );
    }

    /** @test */
    public function it_limits_results_starting_from_offset(): void
    {
        $movies = $this->movieRepository->findBy([], null, 1, 2);

        $this->assertCount(1, $movies);
        $this->assertEquals(
            ['Rambo'],
            $this->mapTitles($movies)
        );
    }

    /**
     * @param array{id: int, title: string, year: int, genre: string} $values
     * @return Movie
     */
    private function createMovie(array $values): Movie
    {
        $movie = new Movie();
        $movie->setId($values['id']);
        $movie->setTitle($values['title']);
        $movie->setYear($values['year']);
        $movie->setGenre($values['genre']);

        return $movie;
    }

    /**
     * @param ObjectCollection<array-key,Movie> $movies
     * @return array<array-key,string|null>
     */
    private function mapTitles(ObjectCollection $movies): array
    {
        return $movies
            ->map(
                function (Movie $movie) {
                    return $movie->getTitle();
                }
            )
            ->getValues()
        ;
    }
}
