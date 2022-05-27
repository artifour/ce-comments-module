<?php

namespace Deti123\Comment\Application\Service;

use Core\Application\Contract\RequestInterface;
use Core\Application\Exception\ValidationException;
use Core\Domain\Exception\NotSavedException;
use Core\Domain\Service\UserServiceInterface;
use Deti123\Comment\Application\Assembler\CommentAssembler;
use Deti123\Comment\Application\Dto\CommentNodeDto;
use Deti123\Comment\Application\Exception\ParentCommentNotFoundHttpException;
use Deti123\Comment\Application\Exception\SpamCommentException;
use Deti123\Comment\Application\Guard\CommentSpamGuard;
use Deti123\Comment\Application\Validator\CommentValidator;
use Deti123\Comment\Domain\Entity\Comment;
use Deti123\Comment\Domain\Factory\CommentFactoryInterface;
use Deti123\Comment\Domain\Repository\CommentRepositoryInterface;
use Deti123\Comment\Domain\ValueObject\CommentRelatedTo;
use Deti123\Comment\Domain\ValueObject\CommentStatus;

class CreateCommentService
{
    public const MESSAGE_PARENT_COMMENT_WAITING_MODERATION = 'Комментарий, на который вы пытаетесь дать ответ, еще ожидает модерации';
    public const MESSAGE_INVALID_PARENT_ID = 'Вы не можете отвечать на комментарии, написанные к другому произведению';

    /** @var CommentRepositoryInterface */
    private $commentRepository;

    /** @var CommentFactoryInterface */
    private $commentFactory;

    /** @var CommentAssembler */
    private $commentTreeAssembler;

    /** @var UserServiceInterface */
    private $userService;

    /** @var CommentValidator */
    private $commentValidator;

    /** @var CommentSpamGuard */
    private $commentSpamGuard;

    /** @var RequestInterface */
    private $request;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        CommentFactoryInterface $commentFactory,
        CommentAssembler $commentTreeAssembler,
        UserServiceInterface $userService,
        CommentValidator $commentValidator,
        CommentSpamGuard $commentSpamGuard,
        RequestInterface $request
    ) {
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
        $this->commentTreeAssembler = $commentTreeAssembler;
        $this->userService = $userService;
        $this->commentValidator = $commentValidator;
        $this->commentSpamGuard = $commentSpamGuard;
        $this->request = $request;
    }

    /**
     * @param string $entity
     * @param int $entityId
     * @param array $attributes
     * @param int|null $parentId
     *
     * @return CommentNodeDto
     *
     * @throws ParentCommentNotFoundHttpException
     * @throws ValidationException
     * @throws SpamCommentException
     * @throws NotSavedException
     */
    public function create(string $entity, int $entityId, array $attributes, ?int $parentId = null): CommentNodeDto
    {
        $user = null;
        $level = 1;

        if ($parentId) {
            $parentComment = $this->getParentComment($parentId, $entity, $entityId);

            $level = $parentComment->level + 1;
        }

        if ($this->userService->isGuest()) {
            $status = CommentStatus::WAITING_MODERATION;
        } else {
            $status = CommentStatus::ACTIVE;
        }

        if ($this->userService->isGuest()) {
            $comment = $this->createByGuest(
                $entity,
                $entityId,
                $attributes['content'],
                $parentId,
                $level,
                new CommentRelatedTo($entity),
                $attributes['url'] ?? null,
                $status,
                $attributes['email'] ?? null,
                $attributes['name']
            );
        } else {
            $comment = $this->createByUser(
                $entity,
                $entityId,
                $attributes['content'],
                $parentId,
                $level,
                new CommentRelatedTo($entity),
                $attributes['url'] ?? null,
                $status,
                $this->userService->getUserId()
            );
        }

        $this->commentValidator->validate($comment);
        $this->commentSpamGuard->guard($comment);

        if (!$this->commentRepository->save($comment)) {
            throw new NotSavedException();
        }

        $commentRoot = $this->commentFactory->create($comment);

        return $this->commentTreeAssembler->assembleByComment($commentRoot);
    }

    /**
     * @param int $parentId
     * @param string $entity
     * @param int $entityId
     *
     * @return Comment
     *
     * @throws ParentCommentNotFoundHttpException
     * @throws ValidationException
     */
    private function getParentComment(int $parentId, string $entity, int $entityId): Comment
    {
        $parentComment = $this->commentRepository->findOneById($parentId);

        if (!$parentComment) {
            throw new ParentCommentNotFoundHttpException();
        }

        if (($parentComment->status != CommentStatus::ACTIVE) && !$this->userService->isAdministrator()) {
            throw new ValidationException(self::MESSAGE_PARENT_COMMENT_WAITING_MODERATION, 'parentId');
        }

        if (($parentComment->entity != $entity) || ($parentComment->entityId != $entityId)) {
            throw new ValidationException(self::MESSAGE_INVALID_PARENT_ID, 'parentId');
        }

        return $parentComment;
    }

    /**
     * @param string $entity
     * @param int $entityId
     * @param string $content
     * @param int|null $parentId
     * @param int $level
     * @param string $relatedTo
     * @param string $url
     * @param int $status
     * @param int $userId
     *
     * @return Comment
     */
    private function createByUser(
        string $entity,
        int $entityId,
        string $content,
        ?int $parentId,
        int $level,
        string $relatedTo,
        ?string $url,
        int $status,
        int $userId
    ): Comment {
        $comment = $this->createDefault(
            $entity,
            $entityId,
            $content,
            $parentId,
            $level,
            $relatedTo,
            $url,
            $status
        );
        $comment->createdBy = $comment->updatedBy = $userId;

        return $comment;
    }

    /**
     * @param string $entity
     * @param int $entityId
     * @param string $content
     * @param int|null $parentId
     * @param int $level
     * @param string $relatedTo
     * @param string $url
     * @param int $status
     * @param string $email
     * @param string $name
     *
     * @return Comment
     */
    private function createByGuest(
        string $entity,
        int $entityId,
        string $content,
        ?int $parentId,
        int $level,
        string $relatedTo,
        ?string $url,
        int $status,
        ?string $email,
        string $name
    ): Comment {
        $comment = $this->createDefault(
            $entity,
            $entityId,
            $content,
            $parentId,
            $level,
            $relatedTo,
            $url,
            $status
        );
        $comment->email = $email;
        $comment->name = $name;

        return $comment;
    }

    /**
     * @param string $entity
     * @param int $entityId
     * @param string $content
     * @param int|null $parentId
     * @param int $level
     * @param string $relatedTo
     * @param string $url
     * @param int $status
     *
     * @return Comment
     */
    private function createDefault(
        string $entity,
        int $entityId,
        string $content,
        ?int $parentId,
        int $level,
        string $relatedTo,
        ?string $url,
        int $status
    ): Comment {
        $comment = new Comment();
        $comment->entity = $entity;
        $comment->entityId = $entityId;
        $comment->content = $content;
        $comment->parentId = $parentId;
        $comment->level = $level;
        $comment->relatedTo = $relatedTo;
        $comment->url = $url;
        $comment->status = $status;
        $comment->createdAt = $comment->updatedAt = time();
        $comment->ipAddress = $this->request->getUserIp();

        return $comment;
    }
}
