<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Modules\Forms\Contracts\FormEditorServiceInterface;
use DbCms\Modules\Forms\Models\CustomForm;
use Illuminate\Contracts\View\View;

final class FormEditGetController extends Controller
{
    public function __construct(private readonly FormEditorServiceInterface $editor) {}

    public function __invoke(CustomForm $form): View
    {
        return view('forms::admin.edit', $this->editor->editing($form)->viewData());
    }
}
