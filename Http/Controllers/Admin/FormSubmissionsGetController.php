<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Http\Controllers\Admin;

use DbCms\Core\DTOs\Admin\DatatableQueryData;
use DbCms\Core\Http\Controllers\Controller;
use DbCms\Core\Services\Admin\DatatableQueryService;
use DbCms\Modules\Forms\Models\CustomForm;
use DbCms\Modules\Forms\Models\FormSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class FormSubmissionsGetController extends Controller
{
    public function __invoke(Request $request, DatatableQueryService $tables): View
    {
        $query = FormSubmission::with('form');

        $submissions = $tables->paginate(
            $query,
            DatatableQueryData::fromInput($request->query()),
            ['ip_address'],
            ['form_id' => 'custom_form_id'],
            'created_at',
            'desc'
        );

        $forms = CustomForm::all();

        return view('forms::admin.submissions', [
            'submissions' => $submissions,
            'forms' => $forms,
        ]);
    }
}
