<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Services;

use DbCms\Core\Contracts\Services\LocalizationServiceInterface;
use DbCms\Core\Forms\SchemaFactory;
use DbCms\Core\Models\Market;
use DbCms\Core\Services\Privacy\LegalPageService;
use DbCms\Modules\Forms\Models\CustomForm;
use DbCms\Modules\Forms\Models\FormField;

final class FormPresentationService
{
    public function __construct(private readonly FormDomainService $forms, private readonly LocalizationServiceInterface $localization, private readonly SchemaFactory $schemas) {}

    /** @param array<string,mixed> $props
     * @return array<string,mixed>
     */
    public function data(array $props): array
    {
        $market = $props['market'] ?? (app()->bound('current.market') ? app('current.market') : null);
        $market = $market instanceof Market ? $market : null;
        $locale = (string) ($props['locale'] ?? app()->getLocale());
        $form = $props['form'] ?? null;
        $form = $form instanceof CustomForm ? $form : $this->forms->getFormBySlug((string) ($props['slug'] ?? 'contacto'), $market);
        if ($form !== null) {
            $form->loadMissing('translations', 'fields.translations');
        }
        $slug = $form->slug ?? (string) ($props['slug'] ?? '');
        $translation = $form?->translation($locale, $market?->id);
        $fields = $form?->fields->map(static function (FormField $field) use ($locale, $market): array {
            $translation = $field->translation($locale, $market?->id);

            return $field->only(['field_key', 'type', 'is_required', 'validation_rules']) + ($translation?->only(['label', 'placeholder', 'options']) ?? []);
        })->all() ?? [];

        return [
            'targetForm' => $form,
            'collectionReady' => app(LegalPageService::class)->canCollect('forms'),
            'url' => $props['actionUrl'] ?? $this->localization->url('/forms/'.$slug.'/submit', $market, $locale),
            'formTitle' => $props['title'] ?? $translation?->name,
            'formDescription' => $translation?->description,
            'btnLabel' => $props['buttonText'] ?? $translation?->submit_button_text,
            'wrapperClass' => ($props['showCard'] ?? true) ? 'cms-card p-6 sm:p-8 rounded-2xl max-w-2xl mx-auto' : 'w-full',
            'schema' => $this->schemas->fromArray($fields),
        ];
    }
}
