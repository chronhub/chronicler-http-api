<?php

declare(strict_types=1);

namespace Chronhub\Projector;

enum Status : string
{
    case RUNNING = 'running';
    case STOPPING = 'stopping';
    case DELETING = 'deleting';
    case DELETING_EMITTED_EVENTS = 'deleting_emitted_events';
    case RESETTING = 'resetting';
    case IDLE = 'idle';
}
