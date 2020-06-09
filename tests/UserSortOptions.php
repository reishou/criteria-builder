<?php

namespace Reishou\Criteria\Tests;

use Reishou\Criteria\SortOptions;

class UserSortOptions extends SortOptions
{
    protected $sorts = [
        'status',
        'name',
    ];

    protected $default = [
        ['status', 'desc'],
        ['name', 'asc'],
    ];
}
