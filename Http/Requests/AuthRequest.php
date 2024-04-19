<?php

namespace Modules\Member\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\BaseRequest;

class AuthRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function registerRules(): array
    {
        return [
            'type' => ['required', Rule::in(['sms', 'mail', 'weixin'])],
            'country_code' => ['required_if:type,sms,weixin'],
            'username' => [
                'required', 'unique:Modules\Member\Models\Member,username',
                Rule::when(fn ($attribute) => $attribute->get('type') === 'sms', ['phone']),
                Rule::when(fn ($attribute) => $attribute->get('type') === 'mail', ['email']),
            ],
            'password' => ['required_if:type,sms,mail', 'between:6,18', 'confirmed'],
            'password_confirmation' => ['required', 'same:password'],
            'code' => ['required', 'check_verify_code'],
            'agree' => ['accepted'],
            'way' => ['required', Rule::in(['1', '2'])],
        ];
    }

    public function loginRules(): array
    {
        return [
            'type' => ['required', Rule::in(['pwd', 'sms', 'mail', 'ticket'])],
            'country_code' => ['required_if:type,sms,weixin'],
            'username' => ['required', 'exclude_unless:type,mail', 'email'],
            'password' => ['required_if:type,pwd', 'between:6,18'],
            'code' => ['required_if:type,sms', 'check_verify_code'],
            'agree' => ['accepted'],
            'way' => ['required', Rule::in(['1', '2'])],
        ];
    }

    public function sendCodeRules(): array
    {
        return [
            'type' => ['required', Rule::in(['sms', 'mail'])],
            'country_code' => ['required_if:type,sms,weixin'],
            'username' => [
                'required',
                Rule::when(fn ($attribute) => $attribute->get('type') === 'sms', ['phone']),
                Rule::when(fn ($attribute) => $attribute->get('type') === 'mail', ['email']),
            ],
            'agree' => ['accepted'],
            'way' => ['required', Rule::in(['1', '2'])],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => '请选择类型',
            'type.in' => '类型选择有误',
            'country_code.required_if' => '请填写国家码',
            'username.required' => '请填写账号',
            'username.unique' => '用户名已存在',
            'username.email' => '邮箱格式不正确',
            'password.required_if' => '请填写密码',
            'password.between' => '密码必须介于6-18个字符之间',
            'password.confirmed' => '密码和确认密码必须匹配',
            'password_confirmation.required' => '请输入确认密码',
            'code.required' => '请填写验证码',
            'agree.accepted' => '请阅读和同意协议',
            'code.required_if' => '请填写验证码',
            'account.required' => '请输入账号',
            'account.sms' => '手机格式不正确',
            'account.email' => '邮箱格式不正确',
            'way.required' => '请选择方式',
            'way.in' => '选择方式有误',
        ];
    }
}
