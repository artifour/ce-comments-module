<?php

namespace Deti123\Comment\Presentation\Form;

use Core\Infrastructure\Form\BaseForm;

class GetCommentsForm extends BaseForm
{
    /** @var string */
    public $entity;

    /** @var int */
    public $entityId;

    /** @var int|null */
    public $parentId;

    /** @var string|null */
    public $filter;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['entity', 'filter'], 'string'],
            [['entityId', 'parentId'], 'integer'],
            [['entity', 'entityId'], 'required'],
        ];
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
     * @return string|null
     */
    public function getFilter(): ?string
    {
        if (!$this->filter) {
            return null;
        }

        return $this->filter;
    }
}
