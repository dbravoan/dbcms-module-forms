<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Services;

use DbCms\Core\Forms\FieldTypeRegistry;
use DbCms\Core\Forms\SchemaFactory;
use DbCms\Core\Models\Market;
use DbCms\Core\Services\Content\PublicTranslation;
use DbCms\Core\Services\Privacy\LegalPageService;
use DbCms\Modules\Forms\Models\CustomForm;
use DbCms\Modules\Forms\Models\FormSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FormDomainService
{
    public function __construct(
        private readonly FieldTypeRegistry $fieldTypes,
        private readonly SchemaFactory $schemaFactory,
    ) {}

    /**
     * Validates dynamic user submission against form schema, verifies honeypot, and persists payload.
     *
     * @param  array<string, mixed>  $rawInput
     */
    public function submitForm(CustomForm $form, array $rawInput, ?Market $market = null, ?string $locale = null): FormSubmission
    {
        app(LegalPageService::class)->requireProcessing('forms');

        // 1. Anti-spam honeypot verification
        if (! empty($rawInput['_hp_check'] ?? '')) {
            throw ValidationException::withMessages(['spam' => 'Spam bot submission blocked.']);
        }

        // Normalize multilingual/alias keys (e.g. name <-> nombre, message <-> mensaje)
        if (isset($rawInput['name']) && ! isset($rawInput['nombre'])) {
            $rawInput['nombre'] = $rawInput['name'];
        }
        if (isset($rawInput['nombre']) && ! isset($rawInput['name'])) {
            $rawInput['name'] = $rawInput['nombre'];
        }
        if (isset($rawInput['message']) && ! isset($rawInput['mensaje'])) {
            $rawInput['mensaje'] = $rawInput['message'];
        }
        if (isset($rawInput['mensaje']) && ! isset($rawInput['message'])) {
            $rawInput['message'] = $rawInput['mensaje'];
        }

        // 2. Compile dynamic validation rules from the registered field types.
        //    A type declared by a disabled module resolves to null and the whole
        //    field (rules included) is ignored — never silently degraded.
        $rules = [];
        $transient = [];

        foreach ($this->schemaFactory->fromModels($form->fields) as $field) {
            $type = $this->fieldTypes->resolve($field->type);

            if ($type === null) {
                continue;
            }

            foreach ($type->rules($field) as $input => $inputRules) {
                $rules[$input] = $inputRules;

                if (! $type->stores()) {
                    $transient[] = $input;
                }
            }
        }

        $validator = Validator::make($rawInput, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = (array) $validator->validated();

        // Anti-spam inputs are validated but never persisted.
        foreach (array_unique($transient) as $input) {
            unset($validatedData[$input]);
        }

        // 3. Store submission
        /** @var FormSubmission $submission */
        $submission = FormSubmission::create([
            'custom_form_id' => $form->id,
            'payload' => $validatedData,
            'locale' => $locale ?? app()->getLocale(),
            'market_id' => $market?->id,
            'ip_address' => config('privacy.forms.collect_network_metadata', false) ? request()->ip() : null,
            'user_agent' => config('privacy.forms.collect_network_metadata', false) ? request()->userAgent() : null,
            'is_reviewed' => false,
        ]);

        return $submission;
    }

    /**
     * Resolves an active form assigned to the given market (or global) by slug.
     */
    public function getFormBySlug(string $slug, ?Market $market = null): ?CustomForm
    {
        return $this->publicQuery($market)->where('slug', $slug)->first();
    }

    /** @return Collection<int, CustomForm> */
    public function getPublicForms(Market $market, string $locale): Collection
    {
        return $this->publicQuery($market)->orderBy('id')->get()
            ->filter(static fn (CustomForm $form): bool => PublicTranslation::resolve($form, $locale, $market) !== null)->values();
    }

    /** @return Builder<CustomForm> */
    private function publicQuery(?Market $market): Builder
    {
        $marketId = $market?->id;

        return CustomForm::query()
            ->where('is_active', true)
            ->when($marketId !== null, function ($query) use ($marketId): void {
                $query->where(function ($nested) use ($marketId): void {
                    $nested->whereHas('markets', static fn ($markets) => $markets->where('markets.id', $marketId))
                        ->orWhereDoesntHave('markets');
                });
            })
            ->with(['translations', 'markets', 'fields.translations']);
    }
}
