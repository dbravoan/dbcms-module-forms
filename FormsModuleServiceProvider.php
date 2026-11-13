<?php

declare(strict_types=1);

namespace DbCms\Modules\Forms;

use DbCms\Core\Blocks\Contracts\BlockRegistryInterface;
use DbCms\Core\Capabilities\Resources\CapabilityResourceRegistry;
use DbCms\Core\Content\Contracts\EntitySchemaRegistryInterface;
use DbCms\Core\Http\Middleware\ResolveMarketAndLocaleMiddleware;
use DbCms\Core\Models\User;
use DbCms\Core\Modules\BaseModuleServiceProvider;
use DbCms\Modules\Forms\Blocks\CustomFormBlock;
use DbCms\Modules\Forms\Capabilities\FormResources;
use DbCms\Modules\Forms\Console\DeliverFormsCommand;
use DbCms\Modules\Forms\Console\FormPrivacyCommand;
use DbCms\Modules\Forms\Contracts\FormAdministrationServiceInterface;
use DbCms\Modules\Forms\Contracts\FormEditorServiceInterface;
use DbCms\Modules\Forms\Http\Controllers\FormSubmitController;
use DbCms\Modules\Forms\Schema\FormEntitySchemas;
use DbCms\Modules\Forms\Services\FormAdministrationService;
use DbCms\Modules\Forms\Services\FormCookieDisclosure;
use DbCms\Modules\Forms\Services\FormEditorService;
use DbCms\Modules\Forms\Services\FormPresentationService;
use DbCms\Modules\Forms\Services\FormsDashboard;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FormsModuleServiceProvider extends BaseModuleServiceProvider
{
    protected string $identifier = 'forms';

    protected string $name = 'Dynamic Form Builder';

    protected bool $isPageable = false;

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/Config/entity_search.php', 'entity_search.sources');
        $this->mergeConfigFrom(__DIR__.'/Config/delivery.php', 'forms_delivery');
        $this->app->singleton(FormCookieDisclosure::class);
        $this->app->tag(FormCookieDisclosure::class, 'cms.cookie-disclosures');
        $this->app->bind(FormAdministrationServiceInterface::class, FormAdministrationService::class);
        $this->app->bind(FormEditorServiceInterface::class, FormEditorService::class);
    }

    public function boot(): void
    {
        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command('forms:deliver')->everyMinute()->withoutOverlapping()->when(static fn (): bool => Schema::hasTable('form_submission_actions'));
            $schedule->command('forms:privacy prune')->daily()->withoutOverlapping()->when(static fn (): bool => Schema::hasTable('form_submissions'));
        });
        if ($this->app->runningInConsole()) {
            $this->commands([FormPrivacyCommand::class, DeliverFormsCommand::class]);
        }
        $this->app->make(CapabilityResourceRegistry::class)->register(FormResources::descriptor());
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/Resources/views', 'forms');
        Blade::anonymousComponentNamespace('forms::components', 'forms');
        \Illuminate\Support\Facades\View::composer('forms::components.form-engine', function (View $view): void {
            $view->with($this->app->make(FormPresentationService::class)->data($view->getData()));
        });
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        if ($this->app->bound(BlockRegistryInterface::class)) {
            $this->app->make(BlockRegistryInterface::class)->register(new CustomFormBlock);
        }

        if ($this->app->bound(EntitySchemaRegistryInterface::class)) {
            foreach (FormEntitySchemas::all() as $schema) {
                $this->app->make(EntitySchemaRegistryInterface::class)->register($schema);
            }
        }

        // Form submission endpoints
        $adminPath = (string) config('cms.admin_path', 'admin');
        $blocked = "admin|{$adminPath}|api|forms|geo|install|media|theme-assets|up";

        Route::middleware(['web', 'module.enabled:forms', ResolveMarketAndLocaleMiddleware::class])
            ->prefix('{market}/{locale}')
            ->where(['market' => "^(?!{$blocked})[a-zA-Z0-9_\-]+$", 'locale' => '[a-zA-Z0-9_\-]+'])
            ->group(function (): void {
                Route::post('/forms/{formSlug}/submit', [FormSubmitController::class, 'submit'])
                    ->name('forms.market.submit');
            });

        Route::middleware(['web', 'module.enabled:forms'])->post('/forms/{formSlug}/submit', [FormSubmitController::class, 'submit'])
            ->name('forms.submit');
    }

    public function getAdminDashboard(User $user): ?array
    {
        return $this->app->make(FormsDashboard::class)->data($user);
    }

    public function getAdminNavigation(): array
    {
        return [[
            'id' => 'forms',
            'title' => __('admin.nav.items.forms'),
            'icon' => 'inbox',
            'children' => [
                ['title' => __('admin.nav.items.forms'), 'route' => 'admin.forms.index', 'activePattern' => ['admin.forms.index', 'admin.forms.create', 'admin.forms.edit', 'admin.forms.store', 'admin.forms.update'], 'icon' => 'file-lines', 'permission' => 'manage:forms'],
                ['title' => __('admin.nav.items.form_submissions'), 'route' => 'admin.forms.submissions', 'activePattern' => 'admin.forms.submissions*', 'icon' => 'inbox', 'permission' => 'manage:forms'],
            ],
        ]];
    }
}
