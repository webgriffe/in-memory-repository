<?php

namespace Webgriffe\InMemoryRepository;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template TKey of array-key
 * @template T of object
 * @template-extends ArrayCollection<TKey,T>
 */
final class ObjectCollection extends ArrayCollection
{
}
