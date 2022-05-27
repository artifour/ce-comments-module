<?php

namespace Deti123\Comment\Infrastructure\DataProvider;

use Core\Application\Dto\PaginationDto;
use Core\Infrastructure\Builder\QueryBuilder;
use Deti123\Comment\Domain\DataProvider\CommentDataProviderInterface;
use Deti123\Comment\Domain\Entity\Comment;
use Deti123\Comment\Domain\ValueObject\CommentStatus;
use Deti123\Comment\Infrastructure\Builder\CommentQueryBuilder;

class CommentDataProvider implements CommentDataProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getAllUndeletedIdsByEntityAndEntityIdAndUserId(
        string $entity,
        int $entityId,
        int $userId,
        ?PaginationDto $pagination = null
    ): array {
        // Запрос верхнеуровневых комментариев
        $query = (new CommentQueryBuilder())
            ->andWhere([Comment::ATTR_LEVEL => 1])
            ->andWhere([Comment::ATTR_ENTITY => $entity])
            ->andWhere([Comment::ATTR_ENTITY_ID => $entityId])
            ->andWhere([
                'OR',
                [Comment::ATTR_STATUS => [CommentStatus::ACTIVE]],
                [
                    'AND',
                    [Comment::ATTR_STATUS => [CommentStatus::WAITING_MODERATION, CommentStatus::SUSPICIOUS]],
                    [Comment::ATTR_CREATED_BY => $userId],
                ]
            ]);

        if ($pagination && ($pagination->totalCount === null)) {
            $pagination->setTotalCount($query->count());
        }

        $topLevelIds = $query
            ->orderBy([Comment::ATTR_ID => SORT_DESC])
            ->pagination($pagination)
            ->column();

        // Запрос дочерних комментариев
        $childIds = (new QueryBuilder())
            ->from([
                'c' => (new CommentQueryBuilder()),
                'i' => "(SELECT @pv := '" . implode(',', $topLevelIds) . "')",
            ])->andWhere('find_in_set(parentId, @pv)')
            ->andWhere("length(@pv := concat(@pv, ',', id))")
            ->andWhere([
                'OR',
                [Comment::COLUMN_STATUS => [CommentStatus::ACTIVE]],
                [
                    'AND',
                    [Comment::COLUMN_STATUS => [CommentStatus::WAITING_MODERATION, CommentStatus::SUSPICIOUS]],
                    [Comment::COLUMN_CREATED_BY => $userId],
                ]
            ])->column();

        return array_merge($topLevelIds, $childIds);
    }

    /**
     * @inheritDoc
     */
    public function getAllByEntityAndEntityIdAndStatus(
        string $entity,
        int $entityId,
        array $status,
        ?PaginationDto $pagination = null
    ): array {
        // Запрос верхнеуровневых комментариев
        $query = (new CommentQueryBuilder())
            ->andWhere([Comment::ATTR_LEVEL => 1])
            ->andWhere([Comment::ATTR_ENTITY => $entity])
            ->andWhere([Comment::ATTR_ENTITY_ID => $entityId])
            ->andWhere([Comment::ATTR_STATUS => $status]);

        if ($pagination && ($pagination->totalCount === null)) {
            $pagination->setTotalCount($query->count());
        }

        $topLevelIds = $query
            ->orderBy([Comment::ATTR_ID => SORT_DESC])
            ->pagination($pagination)
            ->column();

        // Запрос дочерних комментариев
        $childIds = (new QueryBuilder())
            ->from([
                'c' => (new CommentQueryBuilder()),
                'i' => "(SELECT @pv := '" . implode(',', $topLevelIds) . "')",
            ])->andWhere('find_in_set(parentId, @pv)')
            ->andWhere("length(@pv := concat(@pv, ',', id))")
            ->andWhere([Comment::COLUMN_STATUS => $status])
            ->column();

        return array_merge($topLevelIds, $childIds);
    }
}
