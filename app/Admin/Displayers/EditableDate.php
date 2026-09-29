<?php

namespace App\Admin\Displayers;

use Encore\Admin\Grid\Displayers\Editable;

class EditableDate extends Editable
{
    protected function buildEditableOptions(array $arguments = [])
    {
        $this->addOptions(['savenochange' => true]);

        parent::buildEditableOptions($arguments);
    }
}
