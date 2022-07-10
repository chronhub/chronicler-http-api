<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Upcasting;

use Chronhub\Messager\Message\Message;

final class ChainUpcaster implements Upcaster
{
    /**
     * @var Upcaster[]
     */
    private array $upcasters;

    public function __construct(Upcaster ...$upcasters)
    {
        $this->upcasters = $upcasters;
    }

    public function upcast(Message $message): array
    {
        $result = [];
        $messages = [$message];

        foreach ($this->upcasters as $upcaster) {
            $result = [];

            foreach ($messages as $message) {
                $result = array_merge($result, $upcaster->upcast($message));
            }

            $messages = $result;
        }

        return $result;
    }
}
