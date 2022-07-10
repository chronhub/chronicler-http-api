<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Projection provider
    |--------------------------------------------------------------------------
    |
    */

    'provider' => [
        'eloquent' => \Chronhub\Projector\Model\Projection::class,

        'in_memory' => \Chronhub\Projector\Model\InMemoryProjectionProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Projectors
    |--------------------------------------------------------------------------
    |
    | Each projector is tied to an event store
    | caution as Dev is responsible to match connection between various services
    |
    |       chronicler:                 chronicler configuration key
    |       options:                    options key
    |       provider:                   projection provider key
    |       event_stream_provider:      from chronicler configuration key
    |       dispatch_projector_events:  dispatch event projection status (start, restarted, stop, reset, delete, deleteIncl)
    |       scope:                      projection query filter
    */

    'projectors' => [
        'default' => [
            'chronicler' => 'pgsql',
            'options' => 'lazy',
            'provider' => 'eloquent',
            'event_stream_provider' => 'eloquent',
            'dispatch_projector_events' => false,
            'scope' => \Chronhub\Projector\Support\PgsqlProjectionQueryScope::class,
        ],

        'in_memory' => [
            'chronicler' => 'in_memory',
            'options' => 'in_memory',
            'provider' => 'in_memory',
            'event_stream_provider' => 'in_memory',
            'scope' => \Chronhub\Projector\Support\InMemoryProjectionQueryScope::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Projector options
    |--------------------------------------------------------------------------
    |
    | Options can be an array or a service implementing projector option contract
    | with pre-defined options which can not be mutated
    |
    */
    'options' => [
        'default' => [],

        'lazy' => [
            \Chronhub\Projector\Factory\EnumOption::UPDATE_LOCK_THRESHOLD->value => 5000,
        ],

        'in_memory' => \Chronhub\Projector\Support\InMemoryProjectorOption::class,

        'snapshot' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Console and commands
    |--------------------------------------------------------------------------
    |
    */
    'console' => [
        'load_migrations' => true,

        'load_commands' => true,

        'commands' => [
            \Chronhub\Projector\Support\Console\Read\StateOfProjectionCommand::class,
            \Chronhub\Projector\Support\Console\Read\StatusOfProjectionCommand::class,
            \Chronhub\Projector\Support\Console\Read\StreamPositionOfProjectionCommand::class,

            \Chronhub\Projector\Support\Console\Write\StopProjectionCommand::class,
            \Chronhub\Projector\Support\Console\Write\ResetProjectionCommand::class,
            \Chronhub\Projector\Support\Console\Write\DeleteProjectionCommand::class,
            \Chronhub\Projector\Support\Console\Write\DeleteIncProjectionCommand::class,

            \Chronhub\Projector\Support\Console\Query\ProjectAllStreamCommand::class,
            \Chronhub\Projector\Support\Console\Query\ProjectCategoryStreamCommand::class,
            \Chronhub\Projector\Support\Console\Query\ProjectMessageNameCommand::class,
        ],
    ],
];
