<?php

namespace ProjectName\Comment\Application\Exception;

use Core\Application\Exception\NotFoundHttpException;

class ParentCommentNotFoundHttpException extends NotFoundHttpException
{
    public const MESSAGE = 'Комментарий, на который вы пытаетесь дать ответ, не существует';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
