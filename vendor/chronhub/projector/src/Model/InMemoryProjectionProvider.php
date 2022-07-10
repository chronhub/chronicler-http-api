<?php

declare(strict_types=1);

namespace Chronhub\Projector\Model;

use Illuminate\Support\Collection;
use Chronhub\Messager\Support\Clock\Clock;
use Chronhub\Projector\Exception\InvalidArgumentException;
use function in_array;
use function array_key_exists;

final class InMemoryProjectionProvider implements ProjectionProvider
{
    /**
     * @var Collection<InMemoryProjection>
     */
    private Collection $projections;

    private array $fillable = ['state', 'position', 'status', 'locked_until'];

    public function __construct(private Clock $clock)
    {
        $this->projections = new Collection();
    }

    public function createProjection(string $name, string $status): bool
    {
        if ($this->projectionExists($name)) {
            return false;
        }

        $this->projections->put($name, InMemoryProjection::create($name, $status));

        return true;
    }

    public function updateProjection(string $name, array $data): bool
    {
        foreach (array_keys($data) as $key) {
            if (! in_array($key, $this->fillable)) {
                throw new InvalidArgumentException("Invalid projection field $key");
            }
        }

        /** @var InMemoryProjection $projection */
        if (null === $projection = $this->findByName($name)) {
            return false;
        }

        if (isset($data['state'])) {
            $projection->setState($data['state']);
        }

        if (isset($data['position'])) {
            $projection->setPosition($data['position']);
        }

        if (isset($data['status'])) {
            $projection->setStatus($data['status']);
        }

        if (array_key_exists('locked_until', $data)) {
            $projection->setLockedUntil($data['locked_until']);
        }

        return true;
    }

    public function deleteProjectionByName(string $name): bool
    {
        if (! $this->projections->has($name)) {
            return false;
        }

        $this->projections->forget($name);

        return true;
    }

    public function projectionExists(string $name): bool
    {
        return $this->projections->has($name);
    }

    public function findByName(string $name): ?ProjectionModel
    {
        return $this->projections->get($name);
    }

    public function findByNames(string ...$names): array
    {
        return $this->projections
            ->filter(fn (InMemoryProjection $projection, string $name): bool => in_array($name, $names))
            ->keys()
            ->toArray();
    }

    public function acquireLock(string $name, string $status, string $lockedUntil, string $now): bool
    {
        /** @var InMemoryProjection $projection */
        if (null === $projection = $this->findByName($name)) {
            return false;
        }

        if ($this->shouldUpdateLock($projection, $now)) {
            $projection->setStatus($status);
            $projection->setLockedUntil($lockedUntil);

            return true;
        }

        return false;
    }

    private function shouldUpdateLock(ProjectionModel $projection, string $now): bool
    {
        if (null === $projection->lockedUntil()) {
            return true;
        }

        return $this->clock->fromString($now)
            ->after(
                $this->clock->fromString($projection->lockedUntil())
            );
    }
}
