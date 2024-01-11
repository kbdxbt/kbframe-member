<?php

namespace Modules\Member\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\Concerns\SerializeDate;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Member extends User implements JWTSubject
{
    use SerializeDate;
    use SoftDeletes;

    protected $hidden = [
        'password',
    ];

    protected $guarded = [];

    /**
     * 密码加密
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * @param  mixed  $username
     */
    public static function findByUsername($username): mixed
    {
        return self::query()->where('username', $username)->first();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public static function wrapToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('member')->factory()->getTTL() * 60,
        ];
    }
}
