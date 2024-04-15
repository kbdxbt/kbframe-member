<?php

namespace Modules\Member\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Member\Models\Member;

class MemberRepostitory extends BaseRepository
{
    public function model(): string
    {
        return Member::class;
    }
}
