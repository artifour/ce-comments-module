<?php

namespace ProjectName\Comment\Application\Exception;

use Core\Application\Exception\ApplicationException;
use Core\Application\ValueObject\StatusCode;

class SpamCommentException extends ApplicationException
{
    public const MESSAGE = 'Комментарий заблокирован, т. к. содержит спам';

    public function __construct()
    {
        parent::__construct(StatusCode::OK, self::MESSAGE);
    }
}
