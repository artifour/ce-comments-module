<?php

namespace ProjectName\Comment\Domain\ValueObject;

class CommentStatus
{
    public const WAITING_MODERATION = 0;
    public const ACTIVE = 1;
    public const SUSPICIOUS = 3;
    public const DELETED = 2;
}
