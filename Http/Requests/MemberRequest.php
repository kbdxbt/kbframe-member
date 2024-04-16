<?php

namespace Modules\Member\Http\Requests;

use Modules\Core\Http\Requests\BaseRequest;

class MemberRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function updateRules(): array
    {
        return [
            'action' => ['required', 'in:update_pwd'],
            'old_password' => ['required_if:action,update_pwd'],
            'new_password' => ['required_if:action,update_pwd', 'between:6,18'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => '请选择操作',
            'action.in' => '操作有误',
            'old_password.required_if' => '请输入旧密码',
            'new_password.required_if' => '请填写密码',
            'new_password.between' => '密码必须介于6-18个字符之间',
        ];
    }
}
