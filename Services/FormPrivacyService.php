<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms\Services;

use DbCms\Core\Services\Privacy\LegalPageService;
use DbCms\Modules\Forms\Models\FormSubmission;
use Illuminate\Support\Facades\DB;

/** Operates on explicitly identified records, including retained inactive-module data. */
final class FormPrivacyService
{
    /** @param list<int> $ids
     * @return list<array<string,mixed>>
     */
    public function export(array $ids): array
    {
        return array_values(FormSubmission::query()->whereIn('id', $ids)->orderBy('id')->get()->map(static fn (FormSubmission $submission): array => $submission->toArray())->all());
    }

    /** @param list<int> $ids */
    public function erase(array $ids): int
    {
        return DB::transaction(fn (): int => FormSubmission::query()->whereIn('id', $ids)->delete());
    }

    public function prune(): int
    {
        $policy = app(LegalPageService::class)->requireProcessing('forms');

        return FormSubmission::query()->where('created_at', '<=', now()->subDays((int) $policy['retention_days']))->delete();
    }
}
