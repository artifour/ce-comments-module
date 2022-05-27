<?php

namespace Deti123\Comment\Application\Command;

use Core\Application\Exception\ForbiddenException;
use Core\Application\Exception\UnauthorizedException;
use Core\Domain\Service\UserServiceInterface;
use Deti123\Comment\Domain\Command\UpdateCommentStatusToDeletedCommandInterface;
use Deti123\Comment\Domain\Repository\CommentRepositoryInterface;

class DeleteCommentCommand
{
    /** @var CommentRepositoryInterface */
    private $commentRepository;

    /** @var UserServiceInterface */
    private $userService;

    /** @var UpdateCommentStatusToDeletedCommandInterface */
    private $updateCommentStatusToDeletedCommand;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        UserServiceInterface $userService,
        UpdateCommentStatusToDeletedCommandInterface $updateCommentStatusToDeletedCommand
    ) {
        $this->commentRepository = $commentRepository;
        $this->userService = $userService;
        $this->updateCommentStatusToDeletedCommand = $updateCommentStatusToDeletedCommand;
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
        $comment = $this->commentRepository->findOneById($id);
        if (!$comment) {
            return false;
        }

        $userId = $this->userService->getUserId();
        if (!$userId) {
            throw new UnauthorizedException();
        }

        if (!$this->userService->isAdministrator() && ($comment->createdBy != $userId)) {
            throw new ForbiddenException();
        }

        return $this->updateCommentStatusToDeletedCommand->execute($id);
    }
}
