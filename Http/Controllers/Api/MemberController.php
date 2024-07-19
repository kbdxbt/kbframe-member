<?php

namespace Modules\Member\Http\Controllers\Api;

use Modules\Core\Http\Controllers\BaseController;
use Modules\Member\Http\Requests\MemberRequest;
use Modules\Member\Http\Resources\MemberResource;
use Modules\Member\Services\MemberService;

class MemberController extends BaseController
{
    public function __construct(MemberRequest $request, MemberService $service)
    {
        $this->request = $request;
        $this->service = $service;
    }

    public function detail()
    {
        return $this->success(MemberResource::make($this->service->getDetail($this->request->userId())));
    }

    public function update()
    {
        if ($this->request->input('_action') == 'update_pwd') {
            $this->service->updatePassword($this->request->validateInput());
        }

        return $this->ok();
    }
}
