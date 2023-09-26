<?php

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Support\Facades\Notification;
use Modules\Common\Enums\StatusEnum;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Member\Http\Requests\AuthRequest;
use Modules\Member\Models\Member;
use Modules\Member\Services\SendCodeService;

class AuthController extends BaseController
{
    /**
     * @var AuthRequest
     */
    private $request;

    public function __construct(AuthRequest $request)
    {
        $this->request = $request;
    }

    public function register()
    {
        $params = $this->request->validated();

        $result = Member::query()->create([
            'username' => $params['username'],
            'password' => $params['password'],
            'mobile' => $params['type'] === 'sms' ? $params['username'] : '',
            'status' => StatusEnum::ENABLED,
        ]);
        if (! $result) {
            return $this->fail('创建用户失败');
        }

        $ttl = config('jwt.ttl');
        $token = auth('member')->setTTL($ttl)->attempt(request(['username', 'password']));

        return $this->success(Member::wrapToken($token));
    }

    public function login()
    {
        $params = $this->request->validated();

        $member = Member::findByUsername($params['username']);
        if (! $member) {
            return $this->fail('账号未注册');
        }

        $ttl = config('jwt.ttl');
        // 记住我
        if ($this->request->filled('remember_me')) {
            $ttl = config('jwt.remember_ttl');
        }
        $token = auth('member')->setTTL($ttl)->attempt(request(['username', 'password']));
        if (! $token) {
            return $this->fail('账号或密码错误', 401);
        }

        if ($member['status'] === StatusEnum::DISABLED) {
            return $this->fail('账号已禁用, 请联系客服');
        }

        return $this->success(Member::wrapToken($token));
    }

    /**
     * 刷新
     */
    public function refresh()
    {
        $token = auth('member')->refresh();

        return $this->success(Member::wrapToken($token));
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        auth('member')->logout();

        return $this->ok();
    }

    public function sendCode()
    {
        $params = $this->request->validated();

        // 生成验证码
        $code = (new SendCodeService('member:'.$params['username']))->throwIfLimit()->generate();

        // 发送验证码
        return $this->success(['code' => tap($code, function ($code) use ($params): void {
            Notification::route('notify', $params['type'])
                ->notify(new VerificationCodeNotification([
                    'from' => config('mail.from.address'),
                    'to' => $params['username'],
                    'code' => $code,
                ]));
        })]);
    }
}
