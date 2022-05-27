<?php

namespace Deti123\Comment\Application\ValueObject;

use Deti123\Comment\Domain\ValueObject\CommentStatus;

class CommentFilter
{
    public const NOT_DELETED = 'not_deleted';
    public const ACTIVE = 'active';
    public const WAITING_MODERATION = 'waiting_moderation';
    public const DELETED = 'deleted';

    /** @var int */
    private $commentStatus;

    public function __construct(int $commentStatus)
    {
        $this->commentStatus = $commentStatus;
    }

    public function __toString()
    {
        switch ($this->commentStatus) {
            case CommentStatus::ACTIVE:
                return self::ACTIVE;
            case CommentStatus::DELETED;
                return self::DELETED;
            case CommentStatus::WAITING_MODERATION:
            default:
                return self::WAITING_MODERATION;
        }
    }
}
