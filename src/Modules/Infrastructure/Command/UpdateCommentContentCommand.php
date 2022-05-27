<?php

namespace Deti123\Comment\Infrastructure\Command;

use Deti123\Comment\Domain\Command\UpdateCommentContentCommandInterface;
use Deti123\Comment\Domain\Entity\Comment;
use Yii;

class UpdateCommentContentCommand implements UpdateCommentContentCommandInterface
{
    /**
     * @inheritDoc
     */
    public function execute(int $id, string $content, int $updatedBy): bool
    {
        try {
            return (bool)Yii::$app->db->createCommand()->update(
                Comment::TABLE_NAME,
                [
                    Comment::ATTR_CONTENT => $content,
                    Comment::ATTR_UPDATED_BY => $updatedBy,
                    Comment::ATTR_UPDATED_AT => time(),
                ],
                [Comment::ATTR_ID => $id]
            )->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
