<?php

namespace Deti123\Comment\Domain\DataProvider;

use Core\Application\Dto\PaginationDto;

interface CommentDataProviderInterface
{
    /**
     * @param string $entity
     * @param int $entityId
     * @param int $userId
     * @param PaginationDto $pagination
     *
     * @return int[]
     */
    public function getAllUndeletedIdsByEntityAndEntityIdAndUserId(
        string $entity,
        int $entityId,
        int $userId,
        ?PaginationDto $pagination = null
    ): array;

    /**
     * @param string $entity
     * @param int $entityId
     * @param array $status
     * @param PaginationDto|null $pagination
     *
     * @return int[]
     */
    public function getAllByEntityAndEntityIdAndStatus(
        string $entity,
        int $entityId,
        array $status,
        ?PaginationDto $pagination = null
    ): array;
}
