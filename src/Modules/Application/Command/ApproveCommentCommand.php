<?php

namespace Deti123\Comment\Application\Command;

use Core\Application\Exception\ForbiddenException;
use Core\Application\Exception\UnauthorizedException;
use Core\Domain\Service\UserServiceInterface;
use Deti123\Comment\Domain\Command\UpdateCommentStatusCommandInterface;
use Deti123\Comment\Domain\ValueObject\CommentStatus;

class ApproveCommentCommand
{
    public const MESSAGE_ACCESS_DENIED = 'У вас нет прав на данную операцию';

    /** @var UserServiceInterface */
    private $userService;

    /** @var UpdateCommentStatusCommandInterface */
    private $updateCommentStatusCommand;

    public function __construct(
        UserServiceInterface $userService,
        UpdateCommentStatusCommandInterface $updateCommentStatusCommand
    ) {
        $this->userService = $userService;
        $this->updateCommentStatusCommand = $updateCommentStatusCommand;
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function execute(int $id): bool
    {
        $userId = $this->userService->getUserId();
        if (!$userId) {
            throw new UnauthorizedException();
        }

        if (!$this->userService->isAdministrator()) {
            throw new ForbiddenException(self::MESSAGE_ACCESS_DENIED);
        }

        return $this->updateCommentStatusCommand->execute($id, CommentStatus::ACTIVE);
    }
}
