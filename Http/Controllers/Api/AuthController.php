<?php

namespace Modules\Member\Http\Controllers\Api;

use Modules\Core\Http\Controllers\BaseController;
use Modules\Member\Http\Requests\AuthRequest;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberService;

class AuthController extends BaseController
{
    protected MemberService $service;

    public function __construct(MemberService $service)
    {
        $this->service = $service;
    }

    public function register(AuthRequest $request)
    {
        $params = $request->validated();

        $this->service->createMember($params);

        $token = $this->service->login($params['username'], $params['password']);

        return $this->success(Member::wrapToken($token));
    }

    public function login(AuthRequest $request)
    {
        $params = $request->validated();

        $token = $this->service->login($params['username'], $params['password']);

        return $this->success(Member::wrapToken($token));
    }

    public function refresh()
    {
        return $this->success(Member::wrapToken($this->service->refresh()));
    }

    public function logout()
    {
        $this->service->logout();

        return $this->ok();
    }

    public function sendCode(AuthRequest $request)
    {
        return $this->success(['code' => $this->service->sendAuthCode($request->validated())]);
    }
}
