<?php

namespace Deti123\Comment\Application\Exception;

use Core\Application\Exception\ValidationException;

class SendingCommentsTooOftenException extends ValidationException
{
    public const MESSAGE = 'Вы отправляете сообщения слишком часто';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
