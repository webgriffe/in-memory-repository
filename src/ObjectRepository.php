<?php

namespace Webgriffe\InMemoryRepository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ObjectRepository as DoctrineObjectRepository;
use UnexpectedValueException;

/**
 * @template TKey of array-key
 * @template T of object
 * @implements DoctrineObjectRepository<T>
 * @template-implements DoctrineObjectRepository<T>
 * @api
 */
abstract class ObjectRepository implements DoctrineObjectRepository
{
    /** @var ObjectCollection<TKey,T> */
    public ObjectCollection $objectCollection;

    /**
     * @param ObjectCollection<TKey,T>|null $objectCollection
     */
    public function __construct(?ObjectCollection $objectCollection = null)
    {
        if ($objectCollection === null) {
            /** @var ObjectCollection<TKey,T> $objectCollection */
            $objectCollection = new ObjectCollection();
        }
        $this->objectCollection = $objectCollection;
    }

    /**
     * @param mixed $id
     * @return T|null
     */
    #[\Override]
    public function find($id): null|object
    {
        return $this->findOneBy([$this->getIdProperty() => $id]);
    }

    /**
     * @return array<TKey,T>
     */
    #[\Override]
    public function findAll(): array
    {
        return $this->findBy([]);
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<TKey,T>
     *
     * @throws UnexpectedValueException
     */
    #[\Override]
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $criteriaObject = Criteria::create();
        /** @psalm-suppress MixedAssignment */
        foreach ($criteria as $field => $value) {
            $criteriaObject->andWhere(Criteria::expr()->eq($field, $value));
        }
        if ($orderBy !== null) {
            $criteriaObject->orderBy($orderBy);
        }
        $criteriaObject->setMaxResults($limit);
        $criteriaObject->setFirstResult($offset);

        /** @var ObjectCollection<TKey,T> $matching */
        $matching = $this->objectCollection->matching($criteriaObject);

        /** @var array<TKey, T> $result */
        $result = array_values($matching->toArray());

        return $result;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return T|null
     */
    #[\Override]
    public function findOneBy(array $criteria): object|null
    {
        /** @var T[] $results */
        $results = $this->findBy($criteria);
        if (!empty($results)) {
            return reset($results);
        }

        return null;
    }

    /**
     * @return class-string<T>
     */
    #[\Override]
    abstract public function getClassName(): string;

    abstract protected function getIdProperty(): string;
}
