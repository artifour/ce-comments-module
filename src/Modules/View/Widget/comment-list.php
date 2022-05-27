<?php

use Core\Application\Dto\PaginationDto;
use Deti123\Comment\Application\Dto\CommentNodeDto;
use yii\web\View;

/**
 * @var CommentNodeDto[] $comments
 * @var PaginationDto|null $pagination
 * @var bool $moderator
 * @var bool $guest
 *
 * @var View $this
 */
?>

<?php foreach ($comments as $comment): ?>
    <?= $this->render('comment-item', [
        'comment' => $comment,
        'moderator' => $moderator,
        'guest' => $guest,
    ]) ?>
<?php endforeach ?>

<?php if ($pagination && !$pagination->isLastPage()): ?>
    <div class="comment-load-more">
        <div data-page="<?= $pagination->page + 1 ?>"
             onclick="commentWidget.loadMore(this)"
             class="btn btn-primary w-100 cursor-pointer">
            Загрузить еще
        </div>

        <div class="loaded" style="display: none">
            <span><i class="far fa-spinner fa-spin"></i> Загрузка</span>
        </div>
    </div>
<?php endif ?>
