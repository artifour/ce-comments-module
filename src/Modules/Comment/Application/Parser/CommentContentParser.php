<?php

namespace ProjectName\Comment\Application\Parser;

use yii\helpers\Html;

class CommentContentParser
{
    /**
     * @param string $content
     *
     * @return string
     */
    public function parse(string $content): string
    {
        return Html::encode($content);
    }
}
