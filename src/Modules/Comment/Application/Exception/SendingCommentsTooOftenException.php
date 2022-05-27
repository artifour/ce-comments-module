<?php

namespace ProjectName\Comment\Application\Exception;

use Core\Application\Exception\ValidationException;

class SendingCommentsTooOftenException extends ValidationException
{
    public const MESSAGE = 'Вы отправляете сообщения слишком часто';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
