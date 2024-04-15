<?php

namespace Modules\Member\Http\Controllers\Api;

use Modules\Core\Enums\StatusEnum;
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

    public function info(MemberRequest $request)
    {
        return $this->success(MemberResource::make($this->service->getDetail($request->userId())));
    }

    public function updatePassword(MemberRequest $request)
    {
        $this->service->updatePassword($request->validated());

        return $this->ok();
    }
}
