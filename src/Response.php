<?php

namespace inisire\Protocol\MiIO;

class Response
{
    public function __construct(
        private readonly int   $id,
        private readonly array $data = []
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }
}