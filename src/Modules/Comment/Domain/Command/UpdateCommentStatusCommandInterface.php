<?php

namespace ProjectName\Comment\Domain\Command;

interface UpdateCommentStatusCommandInterface
{
    /**
     * @param int $id
     * @param int $status
     *
     * @return bool
     */
    public function execute(int $id, int $status): bool;
}
