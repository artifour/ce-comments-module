<?php

namespace ProjectName\Comment\Infrastructure\Builder;

use Core\Infrastructure\Builder\QueryBuilder;
use ProjectName\Comment\Domain\Entity\Comment;

class CommentQueryBuilder extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Comment::TABLE_NAME);
    }
}
