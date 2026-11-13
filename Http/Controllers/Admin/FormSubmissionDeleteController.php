<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Modules\Forms\Models\FormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

final class FormSubmissionDeleteController extends Controller
{
    public function __invoke(FormSubmission $submission): RedirectResponse
    {
        DB::transaction(static fn () => $submission->delete());

        return redirect()->route('admin.forms.submissions')->with('success', 'Envío de lead eliminado correctamente.');
    }
}
