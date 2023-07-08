<?php

namespace inisire\Protocol\MiIO\Packet;

use inisire\Protocol\MiIO\Handshake;


class Call extends Generic
{
    public function __construct(
        readonly int       $id,
        readonly string    $method,
        readonly array     $params,
        readonly Handshake $handshake,
        readonly string    $secret
    )
    {
        parent::__construct(
            $this->handshake->getDeviceType(),
            $this->handshake->getDeviceId(),
            time() + ($handshake->getTimestamp() - $handshake->getCompletedAt()),
            json_encode(['id' => $id, 'method' => $method, 'params' => $params]),
            $this->secret
        );
    }
}