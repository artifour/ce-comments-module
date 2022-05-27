<?php

namespace Deti123\Comment\Application\Dto;

use Core\Application\Dto\LinkDto;
use Core\Application\ValueObject\TimePassed;
use Deti123\Comment\Application\ValueObject\CommentFilter;
use JsonSerializable;

class CommentNodeDto implements JsonSerializable
{
    /** @var int */
    public $id;

    /** @var LinkDto|string */
    public $name;

    /** @var string */
    public $content;

    /** @var string */
    public $avatarUrl;

    /** @var TimePassed */
    public $createdAt;

    /** @var int */
    public $status;

    /** @var string */
    public $ipAddress;

    /** @var int */
    public $commentsCount = 0;

    /** @var CommentNodeDto[] */
    public $comments = [];

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'avatar' => $this->avatarUrl,
            'createdAt' => $this->createdAt->getFulDateTime(),
            'status' => new CommentFilter($this->status),
            'comments' => $this->comments,
        ];
    }
}
