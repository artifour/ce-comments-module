<?php

namespace ProjectName\Comment\Application\Assembler;

use Core\Application\Dto\LinkDto;
use Core\Application\ValueObject\TimePassed;
use ProjectName\Comment\Application\Dto\CommentNodeDto;
use ProjectName\Comment\Application\Parser\CommentContentParser;
use ProjectName\Comment\Application\ValueObject\RabbitAvatarSVG;
use ProjectName\Comment\Domain\Aggregate\CommentRoot;

class CommentAssembler
{
    /** @var CommentContentParser */
    private $commentContentParser;

    public function __construct(CommentContentParser $commentContentParser)
    {
        $this->commentContentParser = $commentContentParser;
    }

    /**
     * @param CommentRoot $comment
     *
     * @return CommentNodeDto
     */
    public function assembleByComment(CommentRoot $comment): CommentNodeDto
    {
        $node = new CommentNodeDto();
        $node->id = (int)$comment->id;
        if ($comment->getUserId()) {
            $node->name = new LinkDto($comment->getUsername(), '/user/profile/' . $comment->getUserId());
        } else {
            $node->name = $comment->getUsername();
        }

        if (empty($node->name)) {
            $node->name = 'Анонимный пользователь';
        }

        $node->avatarUrl = $comment->getUserAvatar() ?: $this->generateAvatar($comment);

        $node->createdAt = new TimePassed($comment->createdAt);
        $node->status = (int)$comment->status;
        $node->content = $this->commentContentParser->parse($comment->content);
        $node->ipAddress = $comment->ipAddress;

        return $node;
    }

    /**
     * @param CommentRoot[] $comments
     *
     * @return CommentNodeDto[]
     */
    public function assembleTreeByComments(array $comments): array
    {
        return $this->collectChild($comments);
    }

    /**
     * @param CommentRoot[] $comments
     * @param int|null $parentId
     *
     * @return CommentNodeDto[]
     */
    private function collectChild(array &$comments, ?int $parentId = null): array
    {
        $result = [];
        foreach ($comments as $index => $comment) {
            if ($comment->parentId == $parentId) {
                $node = $this->assembleByComment($comment);
                unset($comments[$index]);

                $node->comments = $this->collectChild($comments, $node->id);
                $node->commentsCount = $this->countComments($node->comments);
                $result[] = $node;
            }
        }

        return $result;
    }

    /**
     * @param CommentNodeDto[] $commentNodes
     *
     * @return int
     */
    private function countComments(array $commentNodes): int
    {
        return count($commentNodes) + array_reduce($commentNodes, static function ($count, $commentNode) {
            return $count + $commentNode->commentsCount;
        });
    }

    /**
     * @param CommentRoot $comment
     *
     * @return RabbitAvatarSVG
     */
    private function generateAvatar(CommentRoot $comment): string
    {
        $hash = md5($comment->getUsername());

        return new RabbitAvatarSVG($hash);
    }
}
