<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Exception;

use Chronhub\Chronicler\StreamName;

final class StreamNotFound extends RuntimeException
{
    public static function withStreamName(StreamName $streamName): self
    {
        return new self("Stream {$streamName->toString()} not found");
    }
}
