<?php

declare(strict_types=1);

namespace Chronhub\Projector;

interface ReadModelProjector extends PersistentProjector
{
    public function readModel(): ReadModel;
}
