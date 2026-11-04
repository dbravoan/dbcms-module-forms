<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Http\Controllers\Admin;

use DbCms\Core\Http\Controllers\Controller;
use DbCms\Modules\Forms\Contracts\FormAdministrationServiceInterface;
use DbCms\Modules\Forms\Http\Requests\FormMutationRequest;
use DbCms\Modules\Forms\Models\CustomForm;
use Illuminate\Http\RedirectResponse;

final class FormPutController extends Controller
{
    public function __construct(private readonly FormAdministrationServiceInterface $service) {}

    public function __invoke(FormMutationRequest $request, CustomForm $form): RedirectResponse
    {
        $this->service->update($form, $request->validated());

        return redirect()->route('admin.forms.edit', $form)->with('success', 'Formulario guardado; los cambios publicados pendientes requieren revisión.');
    }
}
