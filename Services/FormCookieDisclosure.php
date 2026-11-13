<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Services;

use DbCms\Core\Modules\ModuleManager;
use DbCms\Core\Security\Contracts\CookieDisclosureContributorInterface;
use DbCms\Modules\Forms\Models\CustomForm;
use Illuminate\Support\Facades\Schema;

final class FormCookieDisclosure implements CookieDisclosureContributorInterface
{
    public function revisionData(): array
    {
        $destinations = [];
        foreach ($this->targets() as $target) {
            $destinations[$target] = (string) app(FormWebhookPolicy::class)->revision($target);
        }
        ksort($destinations);

        return $destinations;
    }

    public function categories(string $locale): array
    {
        $categories = [];
        foreach ($this->targets() as $target) {
            $purpose = config('forms_delivery.webhooks.'.$target.'.purpose');
            if (! is_string($purpose)) {
                continue;
            }
            $previous = $categories[$purpose]['description'] ?? '';
            $categories[$purpose] = ['key' => $purpose, 'title' => ucfirst($purpose),
                'description' => trim($previous.' '.($locale === 'es' ? 'Envío opcional del formulario a ' : 'Optional form delivery to ').$target.'.'),
                'required' => false, 'default' => false, 'cookies' => []];
        }

        return $categories;
    }

    /** @return list<string> */
    private function targets(): array
    {
        if (! Schema::hasTable('custom_forms') || ! Schema::hasColumn('custom_forms', 'actions')
            || ! app(ModuleManager::class)->isModuleEnabled('forms')) {
            return [];
        }
        $targets = [];
        foreach (CustomForm::query()->where('is_active', true)->get() as $form) {
            foreach ($form->actions ?? [] as $action) {
                if (($action['type'] ?? '') === 'webhook' && app(FormWebhookPolicy::class)->available((string) ($action['target'] ?? ''))) {
                    $targets[] = (string) $action['target'];
                }
            }
        }

        return array_values(array_unique($targets));
    }
}
