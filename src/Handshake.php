<?php

namespace inisire\Protocol\MiIO;

class Handshake
{
    private const HANDSHAKE_TTL = 60 * 60;

    public function __construct(
        private readonly int $deviceType,
        private readonly int $deviceId,
        private readonly int $timestamp,
        private readonly int $completedAt
    )
    {
    }

    public function getDeviceType(): int
    {
        return $this->deviceType;
    }

    public function getDeviceId(): int
    {
        return $this->deviceId;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getCompletedAt(): int
    {
        return $this->completedAt;
    }

    public function isValid(): bool
    {
        return time() - $this->completedAt < self::HANDSHAKE_TTL;
    }
}