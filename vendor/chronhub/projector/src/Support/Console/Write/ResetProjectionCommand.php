<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Write;

use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Support\Console\WriteProjectionCommand;

final class ResetProjectionCommand extends WriteProjectionCommand
{
    protected $signature = 'projector:write-reset {stream} {--projector=default}';

    protected $description = 'reset projection by stream name';

    protected function processProjection(StreamName $streamName): void
    {
        $this->projector()->reset($streamName->toString());
    }

    protected function operation(): string
    {
        return 'reset';
    }
}
