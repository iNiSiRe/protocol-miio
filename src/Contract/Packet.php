<?php

namespace inisire\Protocol\MiIO\Contract;

interface Packet
{
    public function toBytes(): string;
}