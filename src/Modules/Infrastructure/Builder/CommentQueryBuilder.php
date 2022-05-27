<?php

namespace Deti123\Comment\Infrastructure\Builder;

use Core\Infrastructure\Builder\QueryBuilder;
use Deti123\Comment\Domain\Entity\Comment;

class CommentQueryBuilder extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Comment::TABLE_NAME);
    }
}
