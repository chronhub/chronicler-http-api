<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Upcasting;

use Chronhub\Messager\Message\Message;

abstract class SingleEventUpcaster implements Upcaster
{
    public function upcast(Message $message): array
    {
        if (! $this->canUpcast($message)) {
            return [$message];
        }

        return $this->doUpcast($message);
    }

    abstract protected function canUpcast(Message $message): bool;

    /**
     * @param  Message  $message
     * @return array<Message>
     */
    abstract protected function doUpcast(Message $message): array;
}
