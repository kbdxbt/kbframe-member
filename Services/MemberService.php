<?php

namespace Modules\Member\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Core\Enums\StatusEnum;
use Modules\Core\Exceptions\BadRequestException;
use Modules\Core\Services\BaseService;
use Modules\Member\Repositories\MemberRepostitory;
use Modules\System\Enums\Message\ChannelEnum;
use Modules\System\Enums\Message\TypeEnum;
use Modules\System\Services\MessageService;

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
        $this->repository->create([
            'username' => $params['username'],
            'password' => $params['password'],
            'mobile' => $params['type'] === 'sms' ? $params['username'] : '',
            'status' => StatusEnum::ENABLED,
        ]);
    }

    public function login($username, $password, $extra = [])
    {
        $member = $this->repository->query()->firstWhere(['username' => $username]);
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

    public function refreshToken()
    {
        return $this->auth->refresh();
    }

    public function logout(): void
    {
        $this->auth->logout();
    }

    public function sendAuthCode($params): string
    {
        $member = $this->checkAccountReg($params['username'], $params['way']);

        $code = VerifyCodeService::make('member:'.$params['username'])->throwIfLimit()->generate();

        MessageService::instance()->send([
            'recipient_id' => $member['id'],
            'type' => TypeEnum::VERIFY_CODE->value,
            'channel' => ChannelEnum::MAIL->value,
            'subject' => '验证码邮件',
            'content' => '您的验证码为：'.$code,
            'options' => [
                'option' => [
                    'to' => $params['username'],
                    'from' => config('mail.from.address')
                ]
            ]
        ]);

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

    public function checkAccountReg($username, $way): \Illuminate\Database\Eloquent\Model
    {
        $member = $this->repository->query()->firstWhere(['username' => $username]);

        if ($way == 1 && $member) {
            throw new BadRequestException('账号已被注册');
        }
        if ($way == 2 && ! $member) {
            throw new BadRequestException('账号未注册');
        }

        return $member;
    }
}
