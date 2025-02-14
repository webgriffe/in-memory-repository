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
    public function find($id)
    {
        return $this->findOneBy([$this->getIdProperty() => $id]);
    }

    /**
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @return ObjectCollection<TKey,T>
     */
    public function findAll(): ObjectCollection
    {
        return $this->findBy([]);
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return ObjectCollection<TKey,T>
     *
     * @throws UnexpectedValueException
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): ObjectCollection
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

        return $matching;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return T|null
     */
    public function findOneBy(array $criteria)
    {
        /** @var T|false $first */
        $first = $this->findBy($criteria)->first();

        return $first ?: null;
    }

    /**
     * @return class-string<T>
     */
    abstract public function getClassName();

    /**
     * @return string
     */
    abstract protected function getIdProperty();
}
