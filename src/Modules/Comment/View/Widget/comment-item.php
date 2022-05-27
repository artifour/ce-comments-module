<?php

use Core\Application\Dto\LinkDto;
use ProjectName\Comment\Application\Dto\CommentNodeDto;
use ProjectName\Comment\Domain\ValueObject\CommentStatus;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var CommentNodeDto $comment
 * @var bool $moderator
 * @var bool $guest
 *
 * @var View $this
 */

$active = $comment->status === CommentStatus::ACTIVE;
$waitingModeration = in_array($comment->status, [CommentStatus::WAITING_MODERATION, CommentStatus::SUSPICIOUS]);
?>

<div class="<?= $active ? 'post' : 'post inactive' ?>" data-id="<?= $comment->id ?>">
    <div class="img" style='background-image: url("<?= $comment->avatarUrl ?>")'>
        <?php if ($waitingModeration): ?>
            <i class="fa fa-user-clock" title="Ожидает модерации"></i>
        <?php endif ?>
    </div>

    <div class="descr">
        <div class="header">
            <div class="author">
                <?php if ($comment->name instanceof LinkDto): ?>
                    <a href="<?= $comment->name->url ?>"><?= $comment->name->title ?></a>
                <?php else: ?>
                    <span><?= $comment->name ?></span>
                <?php endif ?>
            </div>

            <?php if ($moderator && !$active): ?>
                <?= Html::button('<span class="fa fa-check"></span>',
                    [
                        'class' => 'btn btn-success',
                        'title' => 'Опубликовать комментарий',
                        'onclick' => 'commentWidget.approve(this)',
                    ]
                ) ?>
            <?php endif ?>

            <?php if (!$guest && ($moderator || !$active)): ?>
                <?= ''/*Html::button('<span class="fa fa-pencil"></span>',
                    [
                        'class' => 'btn btn-warning',
                        'title' => 'Изменить комментарий',
                        'onclick' => 'commentWidget.edit(this)',
                    ]
                )*/ ?>

                <?= Html::button('<span class="fa fa-trash"></span>',
                    [
                        'class' => 'btn btn-danger',
                        'title' => 'Удалить комментарий',
                        'onclick' => 'commentWidget.delete(this)',
                    ]
                ) ?>
            <?php endif ?>
        </div>

        <div class="text"><?= $comment->content ?></div>

        <div>
            <span class="date"
                <?= $comment->createdAt->isEarlierThanTwoMonths() ? "data-origin='{$comment->createdAt->getFulDateTime()}'" : '' ?>>
                <?= $comment->createdAt ?>
            </span>

            <?php if ($moderator || $active): ?>
                <a href="javascript:"
                   class="toggle-answer"
                   data-id="<?= $comment->id ?>"
                   onclick="commentWidget.toggleAnswer(this)">ответить</a>
            <?php endif ?>
        </div>

        <div class="line"></div>

        <div class="comment-answer-placeholder" data-id="<?= $comment->id ?>"></div>

        <div class="children">
            <?php if ($comment->comments): ?>
                <?= $this->render('comment-items', [
                    'comments' => $comment->comments,
                    'moderator' => $moderator,
                    'guest' => $guest,
                ]) ?>
            <?php endif ?>
        </div>
    </div>
</div>
