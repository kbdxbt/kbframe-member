<?php

namespace Modules\Member\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Modules\Member\Services\SendCodeService;

class MemberServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Member';

    protected string $moduleNameLower = 'member';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->extendValidator();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
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
