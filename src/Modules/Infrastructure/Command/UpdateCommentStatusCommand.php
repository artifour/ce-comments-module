<?php

namespace Deti123\Comment\Infrastructure\Command;

use Deti123\Comment\Domain\Command\UpdateCommentStatusCommandInterface;
use Deti123\Comment\Domain\Entity\Comment;
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
