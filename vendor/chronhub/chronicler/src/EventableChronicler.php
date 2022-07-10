<?php

declare(strict_types=1);

namespace Chronhub\Chronicler;

use Chronhub\Messager\Tracker\Listener;
use Chronhub\Messager\Tracker\OneTimeListener;

interface EventableChronicler extends ChroniclerDecorator
{
    public const FIRST_COMMIT_EVENT = 'first_commit_stream';

    public const PERSIST_STREAM_EVENT = 'persist_stream';

    public const DELETE_STREAM_EVENT = 'delete_stream';

    public const ALL_STREAM_EVENT = 'all_stream';

    public const ALL_REVERSED_STREAM_EVENT = 'all_reversed_stream';

    public const FILTERED_STREAM_EVENT = 'filtered_stream';

    public const FETCH_STREAM_NAMES = 'fetch_stream_names';

    public const FETCH_CATEGORY_NAMES = 'fetch_category_names';

    public const HAS_STREAM_EVENT = 'has_stream';

    public function subscribe(string $eventName, callable $eventContext, int $priority = 0): Listener;

    public function subscribeOnce(string $eventName, callable $eventContext, int $priority = 0): OneTimeListener;

    public function unsubscribe(Listener ...$eventSubscribers): void;
}
