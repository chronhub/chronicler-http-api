<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Upcasting;

use Chronhub\Messager\Message\Message;

interface Upcaster
{
    /**
     * @param  Message  $message
     * @return array<Message>
     */
    public function upcast(Message $message): array;
}
