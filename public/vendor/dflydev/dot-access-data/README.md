Dot Access Data
===============

[![Latest Version](https://img.shields.io/packagist/v/dflydev/dot-access-data.svg?style=flat-square)](https://packagist.org/packages/dflydev/dot-access-data)
[![Total Downloads](https://img.shields.io/packagist/dt/dflydev/dot-access-data.svg?style=flat-square)](https://packagist.org/packages/dflydev/dot-access-data)
[![Software License](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/workflow/status/dflydev/dflydev-dot-access-data/Tests/main.svg?style=flat-square)](https://github.com/dflydev/dflydev-dot-access-data/actions?query=workflow%3ATests+branch%3Amain)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/dflydev/dflydev-dot-access-data.svg?style=flat-square)](https://scrutinizer-ci.com/g/dflydev/dflydev-dot-access-data/code-structure/)
[![Quality Score](https://img.shields.io/scrutinizer/g/dflydev/dflydev-dot-access-data.svg?style=flat-square)](https://scrutinizer-ci.com/g/dflydev/dflydev-dot-access-data)

Given a deep data structure, access data by dot notation.


Requirements
------------

 * PHP (7.1+)

> For PHP (5.3+) please refer to version `1.0`.


Usage
-----

Abstract example:

```php
use Dflydev\DotAccessData\Data;

$data = new Data;

$data->set('a.b.c', 'C');
$data->set('a.b.d', 'D1');
$data->append('a.b.d', 'D2');
$data->set('a.b.e', ['E0', 'E1', 'E2']);
$data->get('a.b.c');
$data->get('a.b.d');
$data->get('a.b.e');
$data->has('a.b.c');
$data->has('a.b.d.j');
$data->get('some.path.that.does.not.exist', 'some-default-value');
$data->get('some.path.that.does.not.exist');
```

A more concrete example:

```php
use Dflydev\DotAccessData\Data;

$data = new Data([
    'hosts' => [
        'hewey' => [
            'username' => 'hman',
            'password' => 'HPASS',
            'roles'    => ['web'],
        ],
        'dewey' => [
            'username' => 'dman',
            'password' => 'D---S',
            'roles'    => ['web', 'db'],
            'nick'     => 'dewey dman',
        ],
        'lewey' => [
            'username' => 'lman',
            'password' => 'LP@$$',
            'roles'    => ['db'],
        ],
    ],
]);
$username = $data->get('hosts.hewey.username');
$password = $data->get('hosts.hewey.password');
$roles = $data->get('hosts.hewey.roles');
$nick = $data->get('hosts.dewey.nick');
$nick = $data->get('hosts.lewey.nick', 'Unknown');
$dewey = $data->getData('hosts.dewey');
$username = $dewey->get('username');
$password = $dewey->get('password');
$roles = $dewey->get('roles');
$data->remove('hosts.lewey');
$data->append('hosts.hewey.roles', 'db');

$data->set('hosts.april', [
    'username' => 'aman',
    'password' => '@---S',
    'roles'    => ['web'],
]);
$hasKey = $data->has('hosts.dewey.username');
```

`Data` may be used as an array, since it implements `ArrayAccess` interface:

```php
$data->get('name') === $data['name']; // true

$data['name'] = 'Dewey';
$data->set($name, 'Dewey');

isset($data['name']) === $data->has('name');
unset($data['name']);
```

`/` can also be used as a path delimiter:

```php
$data->set('a/b/c', 'd');
echo $data->get('a/b/c'); // "d"

$data->get('a/b/c') === $data->get('a.b.c'); // true
```

License
-------

This library is licensed under the MIT License - see the LICENSE file
for details.


Community
---------

If you have questions or want to help out, join us in the
[#dflydev](irc://irc.freenode.net/#dflydev) channel on irc.freenode.net.
