<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Contracts;

use DbCms\Modules\Forms\DTOs\FormEditorData;
use DbCms\Modules\Forms\Models\CustomForm;

interface FormEditorServiceInterface
{
    public function creation(): FormEditorData;

    public function editing(CustomForm $form): FormEditorData;
}
