<?php
declare(strict_types=1);

namespace Pioniro\Seranking\Entity;

class PositionItem
{
    /**
     * @var int
     */
    protected $position;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $snippet;

    /**
     * @var string
     */
    protected $cacheUrl;

    /**
     * PositionItem constructor.
     * @param int $position
     * @param string $url
     * @param string $title
     * @param string $snippet
     * @param string $cacheUrl
     */
    public function __construct(int $position, string $url, string $title, string $snippet, string $cacheUrl)
    {
        $this->position = $position;
        $this->url = $url;
        $this->title = $title;
        $this->snippet = $snippet;
        $this->cacheUrl = $cacheUrl;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSnippet(): string
    {
        return $this->snippet;
    }

    /**
     * @return string
     */
    public function getCacheUrl(): string
    {
        return $this->cacheUrl;
    }
}