<?php

namespace Modules\Member\Http\Controllers\Api;

use Modules\Core\Http\Controllers\BaseController;
use Modules\Member\Http\Requests\AuthRequest;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberService;

class AuthController extends BaseController
{
    public function __construct(AuthRequest $request, MemberService $service)
    {
        $this->request = $request;
        $this->service = $service;
    }

    public function register()
    {
        $params = $this->request->validateInput();

        $this->service->createMember($params);

        $token = $this->service->login($params['username'], $params['password']);

        return $this->success(Member::wrapToken($token));
    }

    public function login()
    {
        $params = $this->request->validateInput();

        $token = $this->service->login($params['username'], $params['password']);

        return $this->success(Member::wrapToken($token));
    }

    public function refresh()
    {
        return $this->success(Member::wrapToken($this->service->refreshToken()));
    }

    public function logout()
    {
        $this->service->logout();

        return $this->ok();
    }

    public function sendCode()
    {
        return $this->success(['code' => $this->service->sendAuthCode($this->request->validateInput())]);
    }
}
