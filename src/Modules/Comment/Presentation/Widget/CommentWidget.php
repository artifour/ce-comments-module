<?php

namespace Deti123\Comment\Presentation\Widget;

use Core\Infrastructure\Widget\BaseWidget;

class CommentWidget extends BaseWidget
{
    /** @var string */
    public $entity;

    /** @var int */
    public $entityId;

    /** @var bool */
    private $moderator;

    /** @var bool */
    private $guest;

    /**
     * @inheritDoc
     */
    public function getViewPath(): string
    {
        return "@Deti123/Comment/View/Widget";
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
        return $this->render('comment', [
            'entity' => $this->entity,
            'entityId' => $this->entityId,
            'moderator' => $this->moderator,
            'guest' => $this->guest,
        ]);
    }
}
