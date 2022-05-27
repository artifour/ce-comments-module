<?php

namespace ProjectName\Comment\Infrastructure\Repository;

use Core\Infrastructure\Builder\QueryBuilder;
use Core\Infrastructure\Repository\BaseRepository;
use ProjectName\Comment\Infrastructure\Builder\CommentQueryBuilder;
use ProjectName\Comment\Domain\Entity\Comment;
use ProjectName\Comment\Domain\Repository\CommentRepositoryInterface;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
    /**
     * @param int $id
     * @return Comment|null
     */
    public function findOneById(int $id): ?Comment
    {
        $source = (new CommentQueryBuilder())
            ->andWhere([Comment::ATTR_ID => $id])
            ->one();

        if (!$source) {
            return null;
        }

        return $this->mapItem($source, new Comment());
    }

    /**
     * @inheritDoc
     */
    public function findLastOneByIpAddress(string $ipAddress): ?Comment
    {
        $lastId = (new QueryBuilder())
            ->select('MAX(id)')
            ->from(Comment::TABLE_NAME)
            ->where([Comment::ATTR_IP_ADDRESS => $ipAddress]);

        $source = (new CommentQueryBuilder())
            ->andWhere([Comment::ATTR_ID => $lastId])
            ->one();

        if (!$source) {
            return null;
        }

        return $this->mapItem($source, new Comment());
    }
}
