<?php

namespace Deti123\Comment\Application\Command;

use Core\Application\Exception\ForbiddenException;
use Core\Application\Exception\UnauthorizedException;
use Core\Domain\Service\UserServiceInterface;
use Deti123\Comment\Domain\Command\UpdateCommentContentCommandInterface;
use Deti123\Comment\Domain\Repository\CommentRepositoryInterface;
use Deti123\Comment\Domain\ValueObject\CommentStatus;

class UpdateCommentContentCommand
{
    /** @var CommentRepositoryInterface */
    private $commentRepository;

    /** @var UserServiceInterface */
    private $userService;

    /** @var UpdateCommentContentCommandInterface */
    private $updateCommentContentCommand;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        UserServiceInterface $userService,
        UpdateCommentContentCommandInterface $updateCommentContentCommand
    ) {
        $this->commentRepository = $commentRepository;
        $this->userService = $userService;
        $this->updateCommentContentCommand = $updateCommentContentCommand;
    }

    /**
     * @param int $id
     * @param string $content
     *
     * @return bool
     *
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function execute(int $id, string $content): bool
    {
        $comment = $this->commentRepository->findOneById($id);
        if (!$comment) {
            return false;
        }

        $userId = $this->userService->getUserId();
        if (!$userId) {
            throw new UnauthorizedException();
        }

        if (!$this->userService->isAdministrator()
            && (($comment->createdBy != $userId) || ($comment->status != CommentStatus::WAITING_MODERATION))) {
            throw new ForbiddenException();
        }

        return $this->updateCommentContentCommand->execute($id, $content, $userId);
    }
}
