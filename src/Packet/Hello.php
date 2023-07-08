<?php

namespace inisire\Protocol\MiIO\Packet;

use inisire\Protocol\MiIO\Contract\Packet;

class Hello implements Packet
{
    public function toBytes(): string
    {
        return hex2bin('21310020ffffffffffffffffffffffffffffffffffffffffffffffffffffffff');
    }
}