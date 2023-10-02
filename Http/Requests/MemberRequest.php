<?php

namespace Modules\Member\Http\Requests;

use Modules\Core\Http\Requests\BaseRequest;

class MemberRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function updatePasswordRules(): array
    {
        return [
            'old_password' => ['required'],
            'new_password' => ['required', 'between:6,18'],
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => '请输入旧密码',
            'new_password.required' => '请填写密码',
            'new_password.between' => '密码必须介于6-18个字符之间',
        ];
    }
}
