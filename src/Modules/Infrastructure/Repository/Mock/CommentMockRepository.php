<?php

namespace Deti123\Comment\Infrastructure\Repository\Mock;

use Core\Domain\Repository\BaseMockRepository;
use Deti123\Comment\Domain\Entity\Comment;
use Deti123\Comment\Domain\Repository\CommentRepositoryInterface;
use Deti123\Comment\Domain\ValueObject\CommentEntity;
use Deti123\Comment\Domain\ValueObject\CommentRelatedTo;
use Deti123\Comment\Domain\ValueObject\CommentStatus;
use Deti123\User\Domain\Entity\User;
use Deti123\User\Domain\Repository\UserRepositoryInterface;
use Deti123\User\Infrastructure\Repository\Mock\UserMockRepository;
use yii\helpers\ArrayHelper;

class CommentMockRepository extends BaseMockRepository implements CommentRepositoryInterface
{
    /** @var UserMockRepository */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function findOneById(int $id): ?Comment
    {
        return $this->findOneByColumnValues(['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function findLastOneByIpAddress(string $ipAddress): ?Comment
    {
        $comments = $this->findAllByColumnValues(['ipAddress' => $ipAddress]);
        if (!$comments) {
            return null;
        }

        ArrayHelper::multisort($comments, 'id', SORT_DESC);

        return $comments[0];
    }

    /**
     * @inheritDoc
     */
    protected function getEntityClass(): string
    {
        return Comment::class;
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [
            'petro' => [
                'id' => $petroCommentId = $this->getNewId(),
                'entity' => CommentEntity::BLOG,
                'entityId' => 1,
                'content' => 'Класс мне понравилось',
                'level' => 1,
                'relatedTo' => CommentRelatedTo::BLOG,
                'status' => CommentStatus::ACTIVE,
                'createdAt' => 1,
                'updatedAt' => 1,
                'name' => 'Petro',
            ],
            'admin' => [
                'entity' => CommentEntity::BLOG,
                'entityId' => 1,
                'content' => 'Да, неплохо',
                'parentId' => $petroCommentId,
                'level' => 2,
                'createdBy' => User::ADMIN_USER_ID,
                'updatedBy' => User::ADMIN_USER_ID,
                'relatedTo' => CommentRelatedTo::BLOG,
                'status' => CommentStatus::ACTIVE,
                'createdAt' => 2,
                'updatedAt' => 2,
            ],
            'vasya' => [
                'entity' => CommentEntity::BLOG,
                'entityId' => 1,
                'content' => 'А мне не понравилось',
                'parentId' => $petroCommentId,
                'level' => 2,
                'relatedTo' => CommentRelatedTo::BLOG,
                'status' => CommentStatus::WAITING_MODERATION,
                'createdAt' => 4,
                'updatedAt' => 4,
                'name' => 'ВасильОК',
            ],
            'user' => [
                'entity' => CommentEntity::BLOG,
                'entityId' => 1,
                'content' => 'Круто',
                'level' => 1,
                'createdBy' => $userId = $this->userRepository->grabFixtures('user')->id,
                'updatedBy' => $userId,
                'relatedTo' => CommentRelatedTo::BLOG,
                'status' => CommentStatus::WAITING_MODERATION,
                'createdAt' => 5,
                'updatedAt' => 5,
            ],
            'bot' => [
                'entity' => CommentEntity::BLOG,
                'entityId' => 1,
                'content' => 'Заходи на сайта <url=site.com>',
                'level' => 1,
                'relatedTo' => CommentRelatedTo::BLOG,
                'status' => CommentStatus::DELETED,
                'createdAt' => 3,
                'updatedAt' => 3,
                'name' => 'Bot',
            ],
        ];
    }
}
