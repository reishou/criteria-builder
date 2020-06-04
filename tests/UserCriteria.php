<?php

namespace Reishou\Criteria\Tests;

use Reishou\Criteria\Criteria;

class UserCriteria extends Criteria
{
    protected $criteria = [
        'status',
        'name' => 'like',
        'age',
    ];
}
