<?php

namespace Deti123\Comment\Domain\Aggregate;

use Core\Domain\Aggregate\BaseAggregate;
use Deti123\Comment\Domain\Entity\Comment;
use Deti123\User\Domain\Entity\User;

/**
 * @property-read int id
 * @property-read string entity
 * @property-read int entityId
 * @property-read string content
 * @property-read int parentId
 * @property-read int level
 * @property-read int createdBy
 * @property-read int updatedBy
 * @property-read string relatedTo
 * @property-read string url
 * @property-read int status
 * @property-read int createdAt
 * @property-read int updatedAt
 * @property-read string email
 * @property-read string name
 * @property-read string ipAddress
 */
class CommentRoot extends BaseAggregate
{
    /** @var Comment */
    private $comment;

    /** @var User|null */
    private $user;

    public function __construct(Comment $comment, ?User $user = null)
    {
        $this->comment = $comment;
        $this->user = $user;
    }

    public function __get($name)
    {
        return $this->comment->{$name};
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        if (!$this->user) {
            return null;
        }

        return $this->user->id;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        if (!$this->user) {
            return $this->comment->name;
        }

        return $this->user->username ?? (string)$this->user->id;
    }

    /**
     * @return string|null
     */
    public function getUserAvatar(): ?string
    {
        if (!$this->user) {
            return null;
        }

        return $this->user->avatar_file;
    }
}
