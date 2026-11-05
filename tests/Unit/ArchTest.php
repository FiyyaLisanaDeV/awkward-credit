<?php

declare(strict_types=1);

arch('aplikasi memakai strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('tidak ada fungsi debug tersisa')
    ->expect(['dd', 'dump', 'var_dump', 'ray'])
    ->not->toBeUsed();

arch('tidak memanggil env() langsung di app')
    ->expect('env')
    ->not->toBeUsedIn('App');
