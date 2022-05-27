<?php

namespace Deti123\Comment\Presentation\Form;

use Codeception\Util\JsonType;
use Core\Infrastructure\Form\BaseForm;

class CreateCommentForm extends BaseForm
{
    /** @var string */
    public $entity;

    /** @var int */
    public $entityId;

    /** @var int */
    public $parentId;

    /** @var array */
    public $comment;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            ['entity', 'string'],
            [['entityId', 'parentId'], 'integer'],
            ['comment', 'safe'],
            [['entity', 'entityId', 'comment'], 'required'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if (!is_array($this->comment)) {
            $this->addError('comment', 'Field "comment" must be an object.');
        }

        if ((new JSONType($this->comment))->matches([
            'content' => 'string',
            'url' => 'string',
        ]) !== true) {
            $this->addError('comment', 'Object "comment" contains errors');
        }

        return true;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        if (!$this->parentId) {
            return null;
        }

        return $this->parentId;
    }

    /**
     * @return array
     */
    public function getComment(): array
    {
        return $this->comment;
    }
}
