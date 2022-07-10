<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Write;

use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Support\Console\WriteProjectionCommand;

final class DeleteProjectionCommand extends WriteProjectionCommand
{
    protected $signature = 'projector:write-delete {stream} {--projector=default}';

    protected $description = 'delete projection by stream name';

    protected function processProjection(StreamName $streamName): void
    {
        $this->projector()->delete($streamName->toString(), false);
    }

    protected function operation(): string
    {
        return 'delete';
    }
}
