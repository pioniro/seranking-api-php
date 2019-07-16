<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Entity;

use DateTimeInterface;

class PositionTask
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $query;

    /**
     * @var int|null
     */
    protected $engine;

    /**
     * @var string|null
     */
    protected $regionGoogle;

    /**
     * @var int|null
     */
    protected $regionYandex;

    /**
     * @var string|null
     */
    protected $status;

    /**
     * @var DateTimeInterface|null
     */
    protected $createdAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return PositionTask
     */
    public function setId(?int $id): PositionTask
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @param string|null $query
     * @return PositionTask
     */
    public function setQuery(?string $query): PositionTask
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getEngine(): ?int
    {
        return $this->engine;
    }

    /**
     * @param int|null $engine
     * @return PositionTask
     */
    public function setEngine(?int $engine): PositionTask
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegionGoogle(): ?string
    {
        return $this->regionGoogle;
    }

    /**
     * @param string|null $regionGoogle
     * @return PositionTask
     */
    public function setRegionGoogle(?string $regionGoogle): PositionTask
    {
        $this->regionGoogle = $regionGoogle;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRegionYandex(): ?int
    {
        return $this->regionYandex;
    }

    /**
     * @param int|null $regionYandex
     * @return PositionTask
     */
    public function setRegionYandex(?int $regionYandex): PositionTask
    {
        $this->regionYandex = $regionYandex;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return PositionTask
     */
    public function setStatus(?string $status): PositionTask
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface|null $createdAt
     * @return PositionTask
     */
    public function setCreatedAt(?DateTimeInterface $createdAt): PositionTask
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}