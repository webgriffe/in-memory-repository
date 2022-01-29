<?php

namespace Webgriffe\InMemoryRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ObjectRepository as DoctrineObjectRepository;
use UnexpectedValueException;

/**
 * @template TKey of array-key
 * @template T of mixed
 * @implements DoctrineObjectRepository<T>
 * @template-implements DoctrineObjectRepository<T>
 */
abstract class ObjectRepository implements DoctrineObjectRepository
{
    /** @var ArrayCollection<TKey, T> */
    public ArrayCollection $memoryObjectStorage;

    /**
     * @param ArrayCollection<TKey, T>|null $memoryObjectStorage
     */
    public function __construct(ArrayCollection $memoryObjectStorage = null)
    {
        if ($memoryObjectStorage === null) {
            $memoryObjectStorage = new ArrayCollection();
        }
        $this->memoryObjectStorage = $memoryObjectStorage;
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
     * @return ArrayCollection<TKey, T>
     */
    public function findAll(): ArrayCollection
    {
        return $this->findBy([]);
    }

    /**
     * @param array<string, mixed> $criteria
     * @param string[]|null        $orderBy
     * @param int|null             $limit
     * @param int|null             $offset
     *
     * @throws UnexpectedValueException
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @return ArrayCollection<TKey, T>
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): ArrayCollection
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

        $matching = $this->memoryObjectStorage->matching($criteriaObject);
        assert($matching instanceof ArrayCollection);

        return $matching;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return mixed|null
     *
     * @psalm-return T|null
     */
    public function findOneBy(array $criteria)
    {
        return $this->findBy($criteria)->first() ?: null;
    }

    /**
     * @return string
     * @psalm-suppress LessSpecificImplementedReturnType
     * @psalm-return class-string<T>
     */
    abstract public function getClassName();

    /**
     * @return string
     */
    abstract protected function getIdProperty();
}
