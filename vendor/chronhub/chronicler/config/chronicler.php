<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Event stream provider
    |--------------------------------------------------------------------------
    |
    | Must be registered in ioc
    */

    'provider' => [
        'eloquent' => \Chronhub\Chronicler\Driver\Connection\EventStream::class,
        'in_memory' => \Chronhub\Chronicler\Driver\InMemory\InMemoryEventStream::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Event store persistence strategy
    |--------------------------------------------------------------------------
    |
    */

    'strategy' => [
        'default' => 'single',

        /*
         * One stream per aggregate type
         * each aggregate per aggregate id will have his own event store/table
         * eg: account-1234-5678-9101 ...
         */
        'aggregate' => [
            'persistence' => \Chronhub\Chronicler\Driver\Connection\Persistence\PgsqlAggregateStreamPersistence::class,
            'producer' => \Chronhub\Chronicler\Strategy\OneStreamPerAggregate::class,
        ],

        /*
         * Single stream per aggregate
         * each aggregate root would have his own event store/table
         *
         * require pessimistic lock
         */
        'single' => [
            'persistence' => \Chronhub\Chronicler\Driver\Connection\Persistence\PgsqlSingleStreamPersistence::class,
            'producer' => \Chronhub\Chronicler\Strategy\SingleStreamPerAggregate::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event store connection
    |--------------------------------------------------------------------------
    |
    | Gap detection
    |
    | write lock strategy is mandatory for a single strategy to prevent missing events
    | but event if, with a lock, false positive appears due to rollback transaction
    | and auto increment visibility
    | note: the pgsql use advisory lock and required to be under transaction
    |
    */

    'connections' => [
        'default' => 'pgsql',

        'pgsql' => [
            'driver' => 'pgsql',

            'tracking' => [
                'tracker_id' => \Chronhub\Chronicler\Tracking\TrackTransactionalStream::class,
                'subscribers' => [
                    \Chronhub\Chronicler\Tracking\Subscribers\PublishEvents::class,
                ],
            ],

            'options' => [
                'write_lock' => true,
                'use_event_decorator' => true,
            ],

            'scope' => \Chronhub\Chronicler\Driver\Connection\PgsqlQueryScope::class,
            'strategy' => 'default',
            'provider' => 'eloquent',
            'query_loader' => \Chronhub\Chronicler\Driver\Connection\Loader\LazyQueryLoader::class,
        ],

        'projecting' => [
            'driver' => 'pgsql',

            'tracking' => [
                'tracker_id' => \Chronhub\Chronicler\Tracking\TrackStream::class,
            ],

            'options' => [
                'write_lock' => false,
                'use_event_decorator' => false,
            ],

            'scope' => \Chronhub\Chronicler\Driver\Connection\PgsqlQueryScope::class,
            'strategy' => 'default',
            'provider' => 'eloquent',
            'query_loader' => \Chronhub\Chronicler\Driver\Connection\Loader\LazyQueryLoader::class,
        ],

        'in_memory' => [
            'driver' => 'in_memory',
            'strategy' => 'single',
            'provider' => 'in_memory',
            'options' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration and command
    |--------------------------------------------------------------------------
    |
    */
    'console' => [
        'load_migrations' => true,

        'commands' => [
            \Chronhub\Chronicler\Support\Console\CreateEventStreamCommand::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AGGREGATE REPOSITORY
    |--------------------------------------------------------------------------
    |
    */

    'repository' => [

        /*
        |--------------------------------------------------------------------------
        | Event Decorators
        |--------------------------------------------------------------------------
        |
        */

        'use_foundation_decorators' => true,

        /*
        |--------------------------------------------------------------------------
        | Event Decorators
        |--------------------------------------------------------------------------
        |
        | Decorate domain event/ aggregate changed for each AR
        |
        */

        'event_decorators' => [],

        /*
        |--------------------------------------------------------------------------
        | Aggregate Repository
        |--------------------------------------------------------------------------
        |
        | Each aggregate repository is defined by his stream name
        |
        */

        'repositories' => [
            /*
             * Stream name
             *
             */
            'my_stream_name' => [
                /*
                 * Specify your aggregate root class as string or
                 * an array with your aggregate root class with his subclasses
                 */
                'aggregate_type' => [
                    'root' => 'AG class name',
                    'children' => [],
                ],

                /*
                 * Chronicler connection key
                 */
                'chronicler' => 'default',

                /*
                 * Laravel cache config
                 *
                 * meant to faster resetting projection
                 * and should not use by default
                 *
                 * determine aggregate cache key under cache tag
                 * if not provided a tag will be generated as
                 * {identity-aggregate id snake base class}
                 *
                 * size 0 to disable
                 *
                 * @see \Chronhub\Chronicler\Aggregate\AggregateCache
                 */
                'cache' => [
                    'size' => 0,
                    'tag' => 'identity-my_account',
                ],

                /*
                 * Aggregate Event decorators
                 * merge with event decorators above
                 */
                'event_decorators' => [],

                /*
                 * Aggregate snapshot
                 *
                 */
                'snapshot' => [
                    /*
                     * Enable snapshot
                     */
                    'use_snapshot' => false,

                    /*
                     * Snapshot stream name
                     * determine your own snapshot stream name or default: my_stream_name_snapshot
                     */
                    'stream_name' => null,

                    /*
                     * Snapshot store service
                     * must be a service registered in ioc
                     * @see '\Chronhub\Contracts\Snapshotting\SnapshotStore'
                     */
                    'store' => 'snapshot.store.service.id',

                    /*
                     * Snapshot Aggregate Repository
                     */
                    //'repository' => \Chronhub\Snapshot\Aggregate\AggregateSnapshotRepository::class,

                    /*
                     * Persist snapshot every x events
                     */
                    'persist_every_x_events' => 1000,

                    /*
                     * Snapshot projector
                     * name and options are defined in the projector configuration
                     */
                    'projector' => [
                        'name' => 'default',
                        'options' => 'default',
                    ],
                ],
            ],
        ],
    ],
];
