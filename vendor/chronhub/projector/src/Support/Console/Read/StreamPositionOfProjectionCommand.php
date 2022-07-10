<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Read;

use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Support\Console\ReadProjectionCommand;

final class StreamPositionOfProjectionCommand extends ReadProjectionCommand
{
    protected $signature = 'projector:query-position {stream} {--projector=default}';

    protected $description = 'query stream position of projection by stream name';

    protected function processProjection(StreamName $streamName): array
    {
        return $this->projector()->streamPositionsOf($streamName->toString());
    }

    protected function field(): string
    {
        return 'stream position of';
    }
}
