<?php

namespace ProjectName\Comment\Presentation\Widget;

use Core\Application\Dto\PaginationDto;
use Core\Infrastructure\Widget\BaseWidget;
use ProjectName\Comment\Application\Dto\CommentNodeDto;

class CommentListWidget extends BaseWidget
{
    /** @var CommentNodeDto[]|CommentNodeDto */
    public $comments;

    /** @var PaginationDto|null */
    public $pagination;

    /** @var bool */
    private $moderator;

    /** @var bool */
    private $guest;

    /**
     * @inheritDoc
     */
    public function getViewPath(): string
    {
        return "@ProjectName/Comment/View/Widget";
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $this->moderator = $this->userService->isAdministrator();
        $this->guest = $this->userService->isGuest();
    }

    /**
     * @inheritDoc
     */
    public function run(): string
    {
        if (is_array($this->comments)) {
            return $this->render('comment-list', [
                'comments' => $this->comments,
                'pagination' => $this->pagination,
                'moderator' => $this->moderator,
                'guest' => $this->guest,
            ]);
        }

        return $this->render('comment-item', [
            'comment' => $this->comments,
            'moderator' => $this->moderator,
            'guest' => $this->guest,
        ]);
    }
}
