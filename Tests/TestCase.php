<?php

namespace Modules\Member\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Jiannei\Response\Laravel\Providers\LaravelServiceProvider;
use Modules\Common\Providers\CommonServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Member\Http\Controllers\Api\AuthController;
use Modules\Member\Http\Controllers\Api\MemberController;
use Modules\Member\Providers\MemberServiceProvider;
use Nwidart\Modules\LaravelModulesServiceProvider;
use PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider as JwtServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $this->app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('members', static function (Blueprint $blueprint): void {
                $blueprint->bigIncrements('id');
                $blueprint->string('username');
                $blueprint->string('nickname')->nullable();
                $blueprint->string('password');
                $blueprint->string('auth_key')->nullable();
                $blueprint->string('remember_token')->nullable();
                $blueprint->tinyInteger('type')->nullable();
                $blueprint->tinyInteger('source')->nullable();
                $blueprint->string('mobile')->nullable();
                $blueprint->integer('pid')->nullable();
                $blueprint->tinyInteger('is_real_auth')->nullable();
                $blueprint->tinyInteger('is_bind_mobile')->nullable();
                $blueprint->tinyInteger('status');
                $blueprint->json('ext')->nullable();
                $blueprint->timestamps();
                $blueprint->softDeletes();
            });

        DB::table('members')->insert([
            'username' => 'test@163.com',
            'password' => Hash::make(123456),
            'mobile' => 'test@163.com',
            'status' => 1,
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelModulesServiceProvider::class,
            CommonServiceProvider::class,
            CoreServiceProvider::class,
            MemberServiceProvider::class,
            LaravelServiceProvider::class,
            JwtServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('auth.guards.member.driver', 'jwt');

        config()->set('jwt.secret', 'dIZJCx68yDjilhuVQT9G1aBIqNE13wwqEdAyH7133NEDZHjlJGcbYmZ2SYx1OKNQ');

        config()->set('auth.guards.member', [
            'driver' => 'jwt',
            'provider' => 'members',
        ]);

        config()->set('auth.providers.members', [
            'driver' => 'eloquent',
            'model' => \Modules\Member\Models\Member::class,
        ]);

        $app['config']->set('modules.paths.modules', __DIR__.'/../../');
    }

    protected function defineRoutes($router): void
    {
        Route::prefix('v1')->middleware(['api'])->group(function () {

            Route::prefix('auth')->controller(AuthController::class)->name('auth.')->group(function () {
                Route::POST('register', 'register')->name('register');
                Route::POST('login', 'login')->name('login');
                Route::POST('send_code', 'sendCode')->name('send_code');
                Route::GET('refresh', 'refresh')->name('refresh')->middleware(['auth:member']);
                Route::GET('logout', 'logout')->name('logout')->middleware(['auth:member']);
            });

            Route::prefix('member')
                ->controller(MemberController::class)
                ->name('member.')
                ->middleware(['auth:member'])->group(function () {
                    Route::GET('info', 'info')->name('info');
                    Route::POST('update_password', 'updatePassword')->name('update_password');
                });

        });
    }
}
