<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Upcasting;

use Chronhub\Messager\Message\Message;

final class NoOpEventUpcaster implements Upcaster
{
    public function upcast(Message $message): array
    {
        return [$message];
    }
}
