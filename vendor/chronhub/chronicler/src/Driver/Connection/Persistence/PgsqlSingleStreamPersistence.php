<?php

declare(strict_types=1);

namespace Chronhub\Chronicler\Driver\Connection\Persistence;

use Chronhub\Chronicler\StreamName;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Chronhub\Messager\Message\DomainEvent;
use Chronhub\Chronicler\Driver\StreamPersistence;
use Chronhub\Chronicler\Driver\Connection\EventConverter;

final class PgsqlSingleStreamPersistence implements StreamPersistence
{
    public function __construct(private EventConverter $eventConverter)
    {
    }

    public function up(string $tableName): ?callable
    {
        Schema::create($tableName, function (Blueprint $table): void {
            $table->id('no');
            $table->uuid('event_id');
            $table->string('event_type');
            $table->json('content');
            $table->jsonb('headers');
            $table->uuid('aggregate_id');
            $table->string('aggregate_type');
            $table->bigInteger('aggregate_version');
            $table->timestampTz('created_at', 6);
            $table->unique(['aggregate_type', 'aggregate_id', 'aggregate_version']);
        });

        return null;
    }

    public function tableName(StreamName $streamName): string
    {
        return '_'.sha1($streamName->toString());
    }

    public function serializeMessage(DomainEvent $event): array
    {
        return $this->eventConverter->toArray($event, true);
    }

    public function isOneStreamPerAggregate(): bool
    {
        return false;
    }
}
