# CRITERIA BUILDER
Build criteria builder for Laravel by different way.

## Requirements
* PHP >= 7.0
* Laravel >= 5.x
## Installation

```bash
composer require reishou/criteria-builder
```

## Usage

First, you need to create a class extends abstract `Reishou\Criteria\Criteria` 

```php
<?php

namespace App\Criteria;

use Reishou\Criteria\Criteria;

class UserCriteria extends Criteria
{
    protected $criteria = [
        'status',
        'name' => 'like',
        'age',
    ];
}

```

Then, you can use it

```php
$param = ['status' => [1, 3], 'name' => 'Reishou', 'age' => 30];

$query = User::query();
$criteria = new UserCriteria($param);
$criteria->apply($query);

$query->get();
```
Result is list users who have status `1` or `3`, name "like" `Reishou` and age = `30`.
Default, criteria will apply where clause with `AND` operator.
If you want to use where clause with `OR` operator, you should make a custom method.

### Custom method

Add a function to `UserCriteria` with pattern `criteria{KeyNameWithStudlyCapsFormat}`

```php
protected function criteriaStatus($query, $value)
{
    $statuses = is_array($value) ? $value : [$value];
    $query->where(function ($query) use ($statuses) {
        foreach ($statuses as $status) {
            $query->orWhere($this->getTable() . '.status', $status);
        }
    });
}
```

### Sort options

Beside, we can use `SortOptions` to set some sort rules

```bash
/GET <URL>/users?sort=-status,name 
```

Create a class `UserSortOptions` extends `Reishou\Criteria\SortOptions`

```php
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
```

`$sorts` is an array that contains fields be sortable.

Use `UserSortOptions` like as using `UserCriteria`

```php
$param = ['sort' => '-status,name'];

$sort  = new UserSortOptions($param);
$query = User::query();
$sort->apply($query);

$query->get();
```

`-status` means sort column `status` and order by `desc`
`name` means sort column `name` and order by `asc` 

## Testing

Run tests with:

```bash
./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see License File for more information.