<?php

namespace Modules\Member\Http\Controllers\Api;

use Modules\Core\Http\Controllers\BaseController;
use Modules\Member\Http\Requests\MemberRequest;
use Modules\Member\Http\Resources\MemberResource;
use Modules\Member\Services\MemberService;

class MemberController extends BaseController
{
    protected MemberService $service;

    public function __construct(MemberService $service)
    {
        $this->service = $service;
    }

    public function detail(MemberRequest $request)
    {
        return $this->success(MemberResource::make($this->service->getDetail($request->userId())));
    }

    public function update(MemberRequest $request)
    {
        if ($request->action == 'update_pwd') {
            $this->service->updatePassword($request->validateInput());
        }

        return $this->ok();
    }
}
