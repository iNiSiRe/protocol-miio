<?php

namespace inisire\Protocol\MiIO;

use inisire\fibers\Contract\Socket;
use inisire\fibers\Contract\SocketFactory;
use inisire\Protocol\ByteBuffer;
use Psr\Log\LoggerInterface;
use inisire\Protocol\MiIO\Packet\Call;
use inisire\Protocol\MiIO\Packet\Hello;
use inisire\Protocol\MiIO\Packet\Generic;

class Connection
{
    private Socket $socket;

    private ?Handshake $handshake = null;

    public function __construct(
        private readonly string          $host,
        private readonly string          $secret,
        private readonly LoggerInterface $logger,
        private readonly SocketFactory   $socketFactory
    )
    {
        $this->socket = $this->socketFactory->createUDP();
    }

    public function __destruct()
    {
        $this->socket->close();
    }

    public function call(int $id, string $method, array $params = []): Response
    {
        $this->logger->debug(sprintf('Call id=%d, method="%s", params="%s"', $id, $method, json_encode($params)));

        if (!$this->handshake || !$this->handshake->isValid()) {
            $this->handshake = $this->handshake();
        }

        $this->write(new Call($id, $method, $params, $this->handshake, $this->secret));
        $response = $this->read();

        $result = json_decode($response->getData(), true);

        $this->logger->debug(sprintf('Response id=%d, data="%s"', $id, json_encode($result)));

        return new Response($result['id'], $result);
    }

    private function handshake(): ?Handshake
    {
        $this->logger->debug('Handshake');

        $this->write(new Hello());

        if ($packet = $this->read()) {
            return new Handshake(
                $packet->getDeviceType(),
                $packet->getDeviceId(),
                $packet->getTimestamp(),
                time()
            );
        }

        return null;
    }

    private function write(Contract\Packet $packet)
    {
        $bytes = $packet->toBytes();

        return $this->socket->sendTo($this->host, 54321, $bytes);
    }

    private function read(): Generic
    {
        $data = $this->socket->read();
        $buffer = new ByteBuffer($data);

        return Generic::fromBuffer($buffer, $this->secret);
    }
}