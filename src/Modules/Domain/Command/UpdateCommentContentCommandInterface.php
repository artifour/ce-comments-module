<?php

namespace Deti123\Comment\Domain\Command;

interface UpdateCommentContentCommandInterface
{
    /**
     * @param int $id
     * @param string $content
     * @param int $updatedBy
     *
     * @return bool
     */
    public function execute(int $id, string $content, int $updatedBy): bool;
}
