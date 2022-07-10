<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Read;

use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Support\Console\ReadProjectionCommand;

final class StateOfProjectionCommand extends ReadProjectionCommand
{
    protected $signature = 'projector:query-state {stream} {--projector=default}';

    protected $description = 'query stream state of projection by stream name';

    protected function processProjection(StreamName $streamName): array
    {
        return $this->projector()->stateOf($streamName->toString());
    }

    protected function field(): string
    {
        return 'state of';
    }
}
