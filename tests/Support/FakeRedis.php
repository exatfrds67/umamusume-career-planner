<?php

declare(strict_types=1);

namespace Tests\Support;

final class FakeRedis
{
    /** @var array<string, array{type: string, value: mixed}> */
    private array $data = [];

    /** @var array<string, int> */
    private array $expirations = [];

    public function connection(?string $name = null): self
    {
        return $this;
    }

    public function ping(): string
    {
        return 'PONG';
    }

    public function flushdb(): bool
    {
        $this->data = [];
        $this->expirations = [];

        return true;
    }

    public function set(string $key, string $value): bool
    {
        $this->data[$key] = [
            'type' => 'string',
            'value' => $value,
        ];

        return true;
    }

    public function setex(string $key, int $seconds, string $value): bool
    {
        $this->set($key, $value);
        $this->expire($key, $seconds);

        return true;
    }

    public function get(string $key): ?string
    {
        $this->purgeExpiredKey($key);

        if (! isset($this->data[$key])) {
            return null;
        }

        return (string) $this->data[$key]['value'];
    }

    public function exists(string $key): int
    {
        $this->purgeExpiredKey($key);

        return isset($this->data[$key]) ? 1 : 0;
    }

    public function del(string|array $key, string ...$additionalKeys): int
    {
        $keys = is_array($key) ? $key : array_merge([$key], $additionalKeys);
        $deleted = 0;

        foreach ($keys as $item) {
            if (isset($this->data[$item])) {
                unset($this->data[$item], $this->expirations[$item]);
                $deleted++;
            }
        }

        return $deleted;
    }

    public function expire(string $key, int $seconds): bool
    {
        if (! isset($this->data[$key])) {
            return false;
        }

        $this->expirations[$key] = time() + $seconds;

        return true;
    }

    public function incr(string $key): int
    {
        return $this->incrby($key, 1);
    }

    public function incrby(string $key, int $amount): int
    {
        $current = $this->get($key);
        $next = (int) ($current ?? 0) + $amount;
        $this->set($key, (string) $next);

        return $next;
    }

    public function lpush(string $key, string $value): int
    {
        $list = $this->getList($key);
        array_unshift($list, $value);
        $this->setList($key, $list);

        return count($list);
    }

    public function rpush(string $key, string $value): int
    {
        $list = $this->getList($key);
        $list[] = $value;
        $this->setList($key, $list);

        return count($list);
    }

    public function lpop(string $key): ?string
    {
        $list = $this->getList($key);

        if ($list === []) {
            return null;
        }

        $value = array_shift($list);
        $this->setList($key, $list);

        return $value;
    }

    public function lindex(string $key, int $index): ?string
    {
        $list = $this->getList($key);

        if ($index < 0) {
            $index = count($list) + $index;
        }

        return $list[$index] ?? null;
    }

    public function lrange(string $key, int $start, int $stop): array
    {
        $list = $this->getList($key);

        if ($list === []) {
            return [];
        }

        $end = $stop === -1 ? count($list) - 1 : $stop;
        $length = $end - $start + 1;

        if ($length <= 0) {
            return [];
        }

        return array_slice($list, $start, $length);
    }

    public function ltrim(string $key, int $start, int $stop): bool
    {
        $list = $this->getList($key);

        if ($list === []) {
            return true;
        }

        $end = $stop === -1 ? count($list) - 1 : $stop;
        $length = $end - $start + 1;
        $list = $length > 0 ? array_slice($list, $start, $length) : [];

        $this->setList($key, $list);

        return true;
    }

    public function lset(string $key, int $index, string $value): bool
    {
        $list = $this->getList($key);

        if ($index < 0) {
            $index = count($list) + $index;
        }

        if (! isset($list[$index])) {
            return false;
        }

        $list[$index] = $value;
        $this->setList($key, $list);

        return true;
    }

    public function zadd(string $key, float $score, string $member): int
    {
        $set = $this->getZSet($key);
        $isNew = ! array_key_exists($member, $set);
        $set[$member] = $score;
        $this->setZSet($key, $set);

        return $isNew ? 1 : 0;
    }

    public function zremrangebyscore(string $key, string $min, string $max): int
    {
        $set = $this->getZSet($key);
        $minScore = $this->parseScore($min, -INF);
        $maxScore = $this->parseScore($max, INF);
        $removed = 0;

        foreach ($set as $member => $score) {
            if ($score >= $minScore && $score <= $maxScore) {
                unset($set[$member]);
                $removed++;
            }
        }

        $this->setZSet($key, $set);

        return $removed;
    }

    public function zremrangebyrank(string $key, int $start, int $stop): int
    {
        $set = $this->getZSet($key);
        $members = $this->getSortedZSetMembers($set);
        $count = count($members);

        if ($count === 0) {
            return 0;
        }

        $end = $stop === -1 ? $count - 1 : $stop;
        $length = $end - $start + 1;

        if ($length <= 0) {
            return 0;
        }

        $toRemove = array_slice($members, $start, $length);
        foreach ($toRemove as $member) {
            unset($set[$member]);
        }

        $this->setZSet($key, $set);

        return count($toRemove);
    }

    public function zcard(string $key): int
    {
        $set = $this->getZSet($key);

        return count($set);
    }

    public function zpopmin(string $key, int $count = 1): array
    {
        $set = $this->getZSet($key);
        $members = $this->getSortedZSetMembers($set);
        $popped = [];

        for ($i = 0; $i < $count; $i++) {
            $member = array_shift($members);
            if ($member === null) {
                break;
            }

            $popped[$member] = $set[$member];
            unset($set[$member]);
        }

        $this->setZSet($key, $set);

        return $popped;
    }

    public function zrange(string $key, int $start, int $stop): array
    {
        $set = $this->getZSet($key);
        $members = $this->getSortedZSetMembers($set);

        if ($members === []) {
            return [];
        }

        $end = $stop === -1 ? count($members) - 1 : $stop;
        $length = $end - $start + 1;

        if ($length <= 0) {
            return [];
        }

        return array_slice($members, $start, $length);
    }

    public function keys(string $pattern): array
    {
        $this->purgeExpiredKeys();
        $escapedPattern = str_replace(['*', '?'], ['.*', '.'], preg_quote($pattern, '/'));
        $regex = '/^'.$escapedPattern.'$/';

        return array_values(array_filter(
            array_keys($this->data),
            static fn (string $key): bool => (bool) preg_match($regex, $key)
        ));
    }

    private function getList(string $key): array
    {
        $this->purgeExpiredKey($key);

        if (! isset($this->data[$key])) {
            return [];
        }

        if ($this->data[$key]['type'] !== 'list') {
            return [];
        }

        return is_array($this->data[$key]['value']) ? $this->data[$key]['value'] : [];
    }

    private function setList(string $key, array $list): void
    {
        $this->data[$key] = [
            'type' => 'list',
            'value' => array_values($list),
        ];
    }

    private function getZSet(string $key): array
    {
        $this->purgeExpiredKey($key);

        if (! isset($this->data[$key])) {
            return [];
        }

        if ($this->data[$key]['type'] !== 'zset') {
            return [];
        }

        return is_array($this->data[$key]['value']) ? $this->data[$key]['value'] : [];
    }

    private function setZSet(string $key, array $set): void
    {
        $this->data[$key] = [
            'type' => 'zset',
            'value' => $set,
        ];
    }

    private function getSortedZSetMembers(array $set): array
    {
        asort($set);

        return array_keys($set);
    }

    private function parseScore(string $value, float $default): float
    {
        if ($value === '-inf') {
            return -INF;
        }

        if ($value === '+inf' || $value === 'inf') {
            return INF;
        }

        return is_numeric($value) ? (float) $value : $default;
    }

    private function purgeExpiredKeys(): void
    {
        foreach (array_keys($this->expirations) as $key) {
            $this->purgeExpiredKey($key);
        }
    }

    private function purgeExpiredKey(string $key): void
    {
        if (! isset($this->expirations[$key])) {
            return;
        }

        if (time() >= $this->expirations[$key]) {
            unset($this->data[$key], $this->expirations[$key]);
        }
    }
}
