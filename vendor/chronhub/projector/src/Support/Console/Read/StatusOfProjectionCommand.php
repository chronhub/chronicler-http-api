<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Read;

use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Support\Console\ReadProjectionCommand;

final class StatusOfProjectionCommand extends ReadProjectionCommand
{
    protected $signature = 'projector:query-status {stream} {--projector=default}';

    protected $description = 'query status of projection by stream name';

    protected function processProjection(StreamName $streamName): array
    {
        return [$this->projector()->statusOf($streamName->toString())];
    }

    protected function field(): string
    {
        return 'status of';
    }
}
