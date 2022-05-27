<?php

namespace ProjectName\Comment\Application\ValueObject;

class CommentSpamValueThreshold
{
    public const TOTALLY_SPAM = 0.9;
    public const LIKELY_SPAM = 0.50;
    public const SUSPICIOUS = 0.1;
}
