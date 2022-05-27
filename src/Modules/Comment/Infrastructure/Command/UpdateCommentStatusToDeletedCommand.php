<?php

namespace Deti123\Comment\Infrastructure\Command;

use Deti123\Comment\Domain\Command\UpdateCommentStatusToDeletedCommandInterface;
use Deti123\Comment\Domain\Entity\Comment;
use Deti123\Comment\Domain\ValueObject\CommentStatus;
use Yii;

class UpdateCommentStatusToDeletedCommand implements UpdateCommentStatusToDeletedCommandInterface
{
    /**
     * @inheritDoc
     */
    public function execute(int $id): bool
    {
        $ids = $this->findAllChildIdsById($id);
        $ids[] = $id;

        try {
            return (bool)Yii::$app->db->createCommand()->update(
                Comment::TABLE_NAME,
                [Comment::ATTR_STATUS => CommentStatus::DELETED],
                [Comment::ATTR_ID => $ids]
            )->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param int $id
     * @return int[]
     */
    private function findAllChildIdsById(int $id): array
    {
        return Yii::$app->db->createCommand(<<<SQL
    SELECT id
    FROM (SELECT * FROM comment) c, (SELECT @childIds:= '$id') initialisation
    WHERE find_in_set(parentId, @childIds) AND LENGTH(@childIds:= concat(@childIds, ',', id))
SQL
        )->queryColumn();
    }
}
