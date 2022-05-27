<?php

namespace ProjectName\Comment\Application\Exception;

use Core\Application\Exception\ValidationException;

class DuplicateCommentException extends ValidationException
{
    public const MESSAGE = 'Комментарий не был добавлен, т. к. является дубликатом';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
