<?php

namespace ProjectName\Comment\Domain\Command;

interface UpdateCommentStatusToDeletedCommandInterface
{
    /**
     * Устанавливает статус "Удалена" для указанного комментария и всех дочерних.
     *
     * @param int $id
     *
     * @return bool
     */
    public function execute(int $id): bool;
}
