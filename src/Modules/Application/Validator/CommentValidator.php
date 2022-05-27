<?php

namespace Deti123\Comment\Application\Validator;

use Core\Application\Exception\ValidationException;
use Core\Domain\Service\UserServiceInterface;
use Deti123\Comment\Application\ValueObject\CommentUsername;
use Deti123\Comment\Domain\Entity\Comment;

class CommentValidator
{
    public const MESSAGE_INVALID_USERNAME = 'Некорректное имя пользователя';
    public const MESSAGE_CONTENT_CANNOT_BE_EMPTY = 'Пожалуйста, введите ваше сообщение';
    public const MESSAGE_CONTENT_MAX_LENGTH = 'Ваше сообщение слишком большое (макс. длина - {length})';

    public const CONTENT_MAX_LENGTH_FOR_GUEST = 512;
    public const CONTENT_MAX_LENGTH = 1024;

    /** @var UserServiceInterface */
    private $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param Comment $comment
     *
     * @throws ValidationException
     */
    public function validate(Comment $comment): void
    {
        if (!is_string($comment->content) || trim($comment->content) === '') {
            throw new ValidationException(self::MESSAGE_CONTENT_CANNOT_BE_EMPTY, 'content');
        }

        if ($this->userService->isAdministrator()) {
            return;
        }

        if (!$comment->createdBy && !(new CommentUsername($comment->name))->isValid()) {
            throw new ValidationException(self::MESSAGE_INVALID_USERNAME, 'username');
        }

        $contentLength = mb_strlen($comment->content);

        if (($contentLength > self::CONTENT_MAX_LENGTH_FOR_GUEST) && $this->userService->isGuest()) {
            throw new ValidationException(str_replace(
                '{length}',
                self::CONTENT_MAX_LENGTH_FOR_GUEST,
                self::MESSAGE_CONTENT_MAX_LENGTH
            ), 'content');
        }

        if ($contentLength > self::CONTENT_MAX_LENGTH) {
            throw new ValidationException(str_replace(
                '{length}',
                self::CONTENT_MAX_LENGTH,
                self::MESSAGE_CONTENT_MAX_LENGTH
            ), 'content');
        }
    }
}
