<?php

namespace Modules\Member\Providers;

use Illuminate\Support\Facades\Validator;
use Modules\Member\Services\SendCodeService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MemberServiceProvider extends PackageServiceProvider
{
    protected string $moduleName = 'Member';

    protected string $moduleNameLower = 'member';

    public function configurePackage(Package $package): void
    {
        $package->name($this->moduleName);
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->extendValidator();

        parent::boot();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->registerAuthConfig();

        parent::register();
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    protected function registerAuthConfig()
    {
        config()->set([
            'auth.guards.member.driver' => 'jwt',
            'auth.guards.member' => [
                'driver' => 'jwt',
                'provider' => 'members',
            ],
            'auth.providers.members' => [
                'driver' => 'eloquent',
                'model' => \Modules\Member\Models\Member::class,
            ],
        ]);
    }

    /**
     * Extend validator rules.
     */
    protected function extendValidator(): void
    {
        Validator::extend('check_verify_code', function (
            $attribute,
            $value,
            $parameters,
            \Illuminate\Validation\Validator $validator
        ) {
            return (new SendCodeService('member:'.request()->get('username')))->check($value, true)
                || ! app()->isProduction();
        }, '验证码有误');
    }
}
