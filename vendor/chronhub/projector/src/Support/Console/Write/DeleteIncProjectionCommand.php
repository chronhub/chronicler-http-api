<?php

declare(strict_types=1);

namespace Chronhub\Projector\Support\Console\Write;

use Chronhub\Chronicler\StreamName;
use Chronhub\Projector\Support\Console\WriteProjectionCommand;

final class DeleteIncProjectionCommand extends WriteProjectionCommand
{
    protected $signature = 'projector:write-deleteIncl {stream} {--projector=default}';

    protected $description = 'delete projection by stream name with emitted events';

    protected function processProjection(StreamName $streamName): void
    {
        $this->projector()->delete($streamName->toString(), true);
    }

    protected function operation(): string
    {
        return 'delete with emitted events';
    }
}
