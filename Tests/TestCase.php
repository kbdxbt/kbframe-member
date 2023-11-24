<?php

namespace Modules\Member\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Jiannei\Response\Laravel\Providers\LaravelServiceProvider;
use Modules\Common\Providers\CommonServiceProvider;
use Modules\Member\Http\Controllers\Api\AuthController;
use Modules\Member\Http\Controllers\Api\MemberController;
use Modules\Member\Providers\MemberServiceProvider;
use Nwidart\Modules\LaravelModulesServiceProvider;
use PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider as JwtServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $modulePath = __DIR__.'/Modules/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
        $this->clearTestModulePath();
    }

    protected function tearDown(): void
    {
        $this->clearTestModulePath();
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

        config()->set('jwt.secret', 'dIZJCx68yDjilhuVQT9G1aBIqNE13wwqEdAyH7133NEDZHjlJGcbYmZ2SYx1OKNQ');

        $this->registerTestModulePath($app);
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

    protected function registerTestModulePath($app): void
    {
        if (! is_dir($this->modulePath)) {
            File::makeDirectory(path: $this->modulePath);
        }
        if (! is_dir($this->modulePath.'kbframe-test')) {
            File::link(__DIR__.'/../', $this->modulePath.'kbframe-test');
        }

        $app['config']->set('modules.scan.enabled', true);
        $app['config']->set('modules.scan.paths', [__DIR__.'/../vendor/kbdxbt/*', __DIR__.'/../Tests/Modules/*']);
    }

    protected function clearTestModulePath(): void
    {
        if (is_dir($this->modulePath.'kbframe-test')) {
            @rmdir($this->modulePath.'kbframe-test');
        }
        if (is_dir($this->modulePath)) {
            File::deleteDirectory($this->modulePath);
        }
    }
}
