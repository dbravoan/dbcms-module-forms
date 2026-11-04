<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Modules\Forms\Contracts\FormEditorServiceInterface;
use Illuminate\Contracts\View\View;

final class FormCreateGetController extends Controller
{
    public function __construct(private readonly FormEditorServiceInterface $editor) {}

    public function __invoke(): View
    {
        return view('forms::admin.create', $this->editor->creation()->viewData());
    }
}
