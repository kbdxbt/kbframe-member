<?php

namespace Modules\Member\Http\Controllers\Api;

use Modules\Common\Enums\StatusEnum;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Member\Http\Requests\MemberRequest;
use Modules\Member\Http\Resources\MemberResource;
use Modules\Member\Models\Member;

class MemberController extends BaseController
{
    /**
     * @var MemberRequest
     */
    private $request;

    public function __construct(MemberRequest $request)
    {
        $this->request = $request;
    }

    /**
     * 用户信息
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function info()
    {
        $uid = $this->request->userId();
        $data = Member::query()->find($uid);

        return $this->success(MemberResource::make($data));
    }

    /**
     * 修改密码
     */
    public function updatePassword()
    {
        $params = $this->request->validated();

        $uid = $this->request->userId();
        $member = Member::query()->firstWhere([['id' => $uid], ['status' => StatusEnum::ENABLED]]);

        if (! $member) {
            return $this->fail('获取账号信息失败');
        }
        if (! password_verify($params['old_password'], $member->password)) {
            return $this->fail('旧密码错误');
        }
        if (password_verify($params['new_password'], $member->password)) {
            return $this->fail('新密码和旧密码一致, 无需修改');
        }

        $member->password = $params['new_password'];
        if (! $member->save()) {
            return $this->fail('修改密码失败');
        }

        auth('member')->logout();

        return $this->ok();
    }
}
