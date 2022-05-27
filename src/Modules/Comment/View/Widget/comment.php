<?php

use Deti123\Comment\Application\Validator\CommentValidator;
use Deti123\Comment\Application\ValueObject\CommentUsername;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var string $entity
 * @var string $entityId
 * @var bool $moderator
 * @var bool $guest
 *
 * @var View $this
 */

$this->registerJs(
    'const commentUsernameRegExp = new RegExp(' . CommentUsername::VALID_PATTERN . ');' .
    'const commentErrorMessageContentCannotBeEmpty = "' . CommentValidator::MESSAGE_CONTENT_CANNOT_BE_EMPTY . '";',
    $this::POS_HEAD,
    'COMMENT-WIDGET'
);

if ($moderator) {
    $textareaMaxLength = null;
} elseif ($guest) {
    $textareaMaxLength = CommentValidator::CONTENT_MAX_LENGTH_FOR_GUEST;
} else {
    $textareaMaxLength = CommentValidator::CONTENT_MAX_LENGTH;
}
?>

<div class="comment-wrapper">
    <div class="comments row">
        <div class="col-md-12 col-sm-12">
            <div id="comment-count" class="title-block clearfix">
                <h3>Комментарии</h3>
            </div>

            <div class="comments-list-wrapper" data-entity="<?= $entity ?>" data-entity-id="<?= $entityId ?>">
                <div class="comment-form-container">
                    <?php if ($guest): ?>
                        <div class="mb-2 w-50">
                            <input name="username"
                                   class="form-control"
                                   placeholder="Ваше имя"
                                   maxlength="80"
                                   oninput="commentWidget.updateUsername(this)">

                            <div class="invalid-feedback"><?= CommentValidator::MESSAGE_INVALID_USERNAME ?></div>
                        </div>
                    <?php endif ?>

                    <div>
                        <div class="input-group">
                            <textarea name="content"
                                      class="form-control"
                                      rows="4"
                                      <?php if ($textareaMaxLength): ?>
                                          <?= "maxlength=$textareaMaxLength" ?>
                                      <?php endif ?>
                                      placeholder="Ваш комментарий..."></textarea>

                            <div class="input-group-append">
                                <?= Html::button(
                                    '<span class="fa fa-comment-alt"></span>',
                                    [
                                        'class' => 'btn btn-primary comment-submit',
                                        'title' => 'Отправить комментарий',
                                        'onclick' => 'commentWidget.send(this)'
                                    ]
                                ) ?>
                            </div>
                        </div>

                        <div class="invalid-feedback">Ошибка</div>
                    </div>

                    <div class="clearfix"></div>
                </div>

                <?php if ($moderator && false): ?>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons" style="margin-bottom: 10px; width:100%">
                        <label onclick="commentWidget.loadByFilter(this, 'not_deleted')" class="btn btn-secondary active">
                            <input type="radio" name="options" id="option1" autocomplete="off"> Не удаленные
                        </label>

                        <label onclick="commentWidget.loadByFilter(this, 'active')" class="btn btn-secondary">
                            <input type="radio" name="options" id="option2" autocomplete="off"> Опубликованные
                        </label>

                        <label onclick="commentWidget.loadByFilter(this, 'waiting_moderation')" class="btn btn-secondary">
                            <input type="radio" name="options" id="option3" autocomplete="off"> Ожидающие модерации
                        </label>

                        <label onclick="commentWidget.loadByFilter(this, 'deleted')" class="btn btn-secondary">
                            <input type="radio" name="options" id="option3" autocomplete="off"> Удаленные
                        </label>
                    </div>
                <?php endif ?>

                <div class="post placeholder">
                    <div class="img"></div>

                    <div class="descr">
                        <div id="page-loading" class="loading text-center" style="">
                            <span><i class="far fa-spinner fa-spin fa-2x"></i></span>
                        </div>

                        <div class="line"></div>
                    </div>
                </div>

                <div class="comments-list">
                    <div id="page-loading" class="loading text-center">
                        <span><i class="far fa-spinner fa-spin fa-2x"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
