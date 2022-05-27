<?php

namespace ProjectName\Comment\Domain\ValueObject;

class CommentEntity
{
    public const TALE = 'a2d9e98b';
    public const AUDIO = '893fe02e';
    public const SONG = '9ca4d2d6';
    public const BLOG = 'dc4a8e8d';

    /** @var string */
    private $type;

    /** @var int */
    private $id;

    public function __construct(string $entity, int $id)
    {
        $this->type = $entity;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
