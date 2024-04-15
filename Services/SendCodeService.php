<?php

namespace Modules\Member\Services;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Core\Exceptions\BadRequestException;
use Modules\Core\Services\BaseService;
use Modules\Core\Support\Traits\Cacheable;

class SendCodeService extends BaseService
{
    use Cacheable;

    public function __construct($key)
    {
        $this->key = $key;
        $this->cachePrefix = 'verify_code:';
    }

    public function generate($length = 6, $ttl = 600): string
    {
        $code = $this->generateCode($length);

        $this->getCache()->put($this->getCacheKey(), $code, $ttl);

        if (! $this->getCache()->has($this->getCacheKey())) {
            throw new BadRequestException('生成验证码错误');
        }

        return $code;
    }

    public function get()
    {
        return $this->getCache()->get($this->getCacheKey());
    }

    public function throwIfLimit(): static
    {
        if (RateLimiter::tooManyAttempts($this->getCacheKey(), 1)) {
            throw new ThrottleRequestsException('发送验证码过于频繁');
        }

        RateLimiter::hit($this->getCacheKey());

        return $this;
    }

    public function check($code, $is_clear = false): bool
    {
        if ($code !== $this->get()) {
            return false;
        }

        if ($is_clear) {
            $this->clear();
        }

        return true;
    }

    public function clear(): void
    {
        $this->getCache()->forget($this->getCacheKey());
    }

    protected function generateCode($length): string
    {
        return generate_random('numeric', $length);
    }
}
