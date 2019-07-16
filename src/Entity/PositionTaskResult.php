<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Entity;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class PositionTaskResult implements IteratorAggregate
{
    /**
     * @var PositionItem[]
     */
    protected $results;

    /**
     * PositionTaskResult constructor.
     * @param PositionItem[] $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->results);
    }

    /**
     * @return PositionItem[]
     */
    public function getResults(): array
    {
        return $this->results;
    }
}