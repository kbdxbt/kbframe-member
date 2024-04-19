<?php

namespace Modules\Member\Services;

use Modules\Core\Enums\StatusEnum;
use Modules\Core\Exceptions\BadRequestException;
use Modules\Core\Services\BaseService;
use Modules\Member\Repositories\MemberRepostitory;

class MemberService extends BaseService
{
    protected MemberRepostitory $repository;

    protected $auth;

    public function __construct(MemberRepostitory $repository)
    {
        $this->repository = $repository;
        $this->auth = auth('member');
    }

    public function createMember($params): void
    {
        $result = $this->repository->create([
            'username' => $params['username'],
            'password' => $params['password'],
            'mobile' => $params['type'] === 'sms' ? $params['username'] : '',
            'status' => StatusEnum::ENABLED,
        ]);

        if (! $result) {
            throw new BadRequestException('创建用户失败');
        }
    }

    public function login($username, $password, $extra = [])
    {
        $member = $this->repository->query()->where('username', $username)->first();
        if (! $member) {
            throw new BadRequestException('账号未注册');
        }

        $ttl = config('jwt.ttl');
        if (! empty($extra['remember_me'])) {
            $ttl = config('jwt.remember_ttl');
        }

        $token = $this->auth->setTTL($ttl)->attempt(compact('username', 'password'));

        if (! $token) {
            throw new BadRequestException('账号或密码错误');
        }

        if ($member['status'] === StatusEnum::DISABLED) {
            throw new BadRequestException('账号已禁用, 请联系客服');
        }

        return $token;
    }

    public function getDetail($id)
    {
        $data = $this->repository->query()->find($id);
        if (! $data) {
            throw new BadRequestException('获取数据失败');
        }

        return $data;
    }

    public function refresh()
    {
        return $this->auth->refresh();
    }

    public function logout(): void
    {
        $this->auth->logout();
    }

    public function sendAuthCode($params): string
    {
        // 生成验证码
        $code = VerifyCodeService::make('member:'.$params['username'])->throwIfLimit()->generate();

        if ($params['type'] == 'mail') {
            // 发送验证码
            \Guanguans\Notify\Messages\EmailMessage::create()
                ->from(config('mail.from.address'))
                ->to($params['username'])
                ->subject('验证码邮件')
                ->text('您的验证码为：'.$code);
        }

        return $code;
    }

    public function updatePassword($params): void
    {
        $member = $this->repository->query()->firstWhere([
            'id' => request()->userId(),
            'status' => StatusEnum::ENABLED->value,
        ]);

        if (! $member) {
            throw new BadRequestException('获取账号信息失败');
        }
        if (! password_verify($params['old_password'], $member->password)) {
            throw new BadRequestException('旧密码错误');
        }
        if (password_verify($params['new_password'], $member->password)) {
            throw new BadRequestException('新密码和旧密码一致, 无需修改');
        }

        $member->password = $params['new_password'];
        if (! $member->save()) {
            throw new BadRequestException('修改密码失败');
        }

        $this->logout();
    }
}
