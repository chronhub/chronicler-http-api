<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Write;

use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Support\Console\WriteProjectionCommand;

final class StopProjectionCommand extends WriteProjectionCommand
{
    protected $signature = 'projector:write-stop {stream} {--projector=default}';

    protected $description = 'stop projection by stream name';

    protected function processProjection(StreamName $streamName): void
    {
        $this->projector()->stop($streamName->toString());
    }

    protected function operation(): string
    {
        return 'stop';
    }
}
