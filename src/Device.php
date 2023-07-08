<?php

namespace inisire\Protocol\MiIO;


class Device
{
    public function __construct(
        private readonly Connection $connection,
    )
    {
    }

    public function call(string $method, array $params = []): Response
    {
        return $this->connection->call(random_int(100000000, 999999999), $method, $params);
    }
}