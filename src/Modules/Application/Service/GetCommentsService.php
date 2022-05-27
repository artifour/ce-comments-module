<?php

namespace Deti123\Comment\Application\Service;

use Core\Application\Dto\PaginationDto;
use Core\Domain\Service\UserServiceInterface;
use Deti123\Comment\Application\Dto\CommentNodeDto;
use Deti123\Comment\Application\Assembler\CommentAssembler;
use Deti123\Comment\Application\ValueObject\CommentFilter;
use Deti123\Comment\Domain\DataProvider\CommentDataProviderInterface;
use Deti123\Comment\Domain\Factory\CommentFactoryInterface;
use Deti123\Comment\Domain\ValueObject\CommentStatus;

class GetCommentsService
{
    /** @var CommentDataProviderInterface */
    private $commentDataProvider;

    /** @var CommentFactoryInterface */
    private $commentFactory;

    /** @var CommentAssembler */
    private $commentAssembler;

    /** @var UserServiceInterface */
    private $userService;

    public function __construct(
        CommentDataProviderInterface $commentDataProvider,
        CommentFactoryInterface $commentFactory,
        CommentAssembler $commentTreeAssembler,
        UserServiceInterface $userService
    ) {
        $this->commentDataProvider = $commentDataProvider;
        $this->commentFactory = $commentFactory;
        $this->commentAssembler = $commentTreeAssembler;
        $this->userService = $userService;
    }

    /**
     * @param string $entity
     * @param int $entityId
     * @param int|null $parentId
     * @param string $filter
     * @param PaginationDto|null $pagination
     *
     * @return CommentNodeDto[]
     */
    public function execute(
        string $entity,
        int $entityId,
        ?int $parentId = null,
        ?string $filter = CommentFilter::NOT_DELETED,
        ?PaginationDto $pagination = null
    ): array {
        if ($this->userService->isAdministrator()) {
            $status = $this->getStatusByFilter($filter);

            $commentIds = $this->commentDataProvider->getAllByEntityAndEntityIdAndStatus(
                $entity,
                $entityId,
                $status,
                $pagination
            );
        } else {
            $userId = $this->userService->getUserId();

            if ($userId) {
                $commentIds = $this->commentDataProvider->getAllUndeletedIdsByEntityAndEntityIdAndUserId(
                    $entity,
                    $entityId,
                    $userId,
                    $pagination
                );

            } else {
                $commentIds = $this->commentDataProvider->getAllByEntityAndEntityIdAndStatus(
                    $entity,
                    $entityId,
                    [CommentStatus::ACTIVE],
                    $pagination
                );
            }
        }
        $comments = $this->commentFactory->getAllByIds($commentIds);

        return $this->commentAssembler->assembleTreeByComments($comments);
    }

    /**
     * @param string|null $filter
     * @return array
     */
    private function getStatusByFilter(?string $filter): array
    {
        switch ($filter) {
            case CommentFilter::ACTIVE:
                return [CommentStatus::ACTIVE];
            case CommentFilter::WAITING_MODERATION:
                return [CommentStatus::WAITING_MODERATION, CommentStatus::SUSPICIOUS];
            case CommentFilter::DELETED:
                return [CommentStatus::DELETED];
            case CommentFilter::NOT_DELETED:
            default:
                return [CommentStatus::ACTIVE, CommentStatus::WAITING_MODERATION, CommentStatus::SUSPICIOUS];
        }
    }
}
