<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Entity;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class PositionTaskList implements IteratorAggregate
{
    /**
     * @var PositionTask[]
     */
    protected $tasks;

    /**
     * PositionTaskList constructor.
     * @param PositionTask[] $tasks
     */
    public function __construct(array $tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * @return PositionTask[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->tasks);
    }
}