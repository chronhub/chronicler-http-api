<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

interface ChroniclerDecorator extends Chronicler
{
    public function innerChronicler(): Chronicler;
}
