<?php

namespace Deti123\Comment\Domain\ValueObject;

class CommentRelatedTo
{
    public const TALE = 'Tale';
    public const AUDIO = 'Audio';
    public const SONG = 'Pesni';
    public const BLOG = 'Blog';

    /** @var string */
    private $entity;

    public function __construct(string $entity)
    {
        $this->entity = $entity;
    }

    public function __toString()
    {
        switch ($this->entity) {
            case CommentEntity::TALE:
                return self::TALE;
            case CommentEntity::AUDIO:
                return self::AUDIO;
            case CommentEntity::SONG:
                return self::SONG;
            case CommentEntity::BLOG:
                return self::BLOG;
        }

        return '';
    }
}
