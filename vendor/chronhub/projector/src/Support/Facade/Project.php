<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Facade;

use Illuminate\Support\Facades\Facade;
use Chronhub\Projector\ProjectorManager;

/**
 * @method static ProjectorManager create(string $driver = 'default')
 */
final class Project extends Facade
{
    const SERVICE_NAME = 'projector.service.manager';

    protected static function getFacadeAccessor(): string
    {
        return self::SERVICE_NAME;
    }
}
