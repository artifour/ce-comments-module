<?php

namespace ProjectName\Comment\Infrastructure\Command;

use ProjectName\Comment\Domain\Command\UpdateCommentStatusCommandInterface;
use ProjectName\Comment\Domain\Entity\Comment;
use Yii;

class UpdateCommentStatusCommand implements UpdateCommentStatusCommandInterface
{
    /**
     * @inheritDoc
     */
    public function execute(int $id, int $status): bool
    {
        try {
            return (bool)Yii::$app->db->createCommand()->update(
                Comment::TABLE_NAME,
                [Comment::ATTR_STATUS => $status],
                [Comment::ATTR_ID => $id]
            )->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
