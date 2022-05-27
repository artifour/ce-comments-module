<?php

namespace Deti123\Comment\Domain\Repository;

use Core\Domain\Entity\EntityInterface;
use Deti123\Comment\Domain\Entity\Comment;

interface CommentRepositoryInterface
{
    /**
     * @param int $id
     * @return Comment|null
     */
    public function findOneById(int $id): ?Comment;

    /**
     * @param string $ipAddress
     * @return Comment|null
     */
    public function findLastOneByIpAddress(string $ipAddress): ?Comment;

    /**
     * @param Comment|EntityInterface $entity
     * @return bool
     */
    public function save(EntityInterface $entity): bool;
}
