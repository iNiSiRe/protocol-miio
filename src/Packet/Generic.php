<?php

namespace inisire\Protocol\MiIO\Packet;

use inisire\Protocol\ByteBuffer;
use inisire\Protocol\MiIO\Contract;

class Generic implements Contract\Packet
{
    // miIO packet structure:
    //  2 - magic
    //  2 - length
    //  4 - reserved
    //  2 - device type
    //  2 - device id
    //  4 - timestamp
    // 16 - checksum
    //  * - data

    public function __construct(
        private readonly int    $deviceType,
        private readonly int    $deviceId,
        private readonly int    $timestamp,
        private readonly string $data,
        private readonly string $secret
    )
    {
    }

    public function getData(): string
    {
        return $this->data;
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

    public function toBytes(): string
    {
        if ($this->getData()) {
            $key = md5(hex2bin($this->secret), true);
            $iv = md5($key . hex2bin($this->secret), true);
            $encryptedData = openssl_encrypt($this->getData(), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        } else {
            $encryptedData = '';
        }

        $buffer = new ByteBuffer('');
        $buffer->write(hex2bin('2131')); // magic (2 bytes)
        $buffer->writeUnsignedShort(strlen($encryptedData) + 32); // length (2 bytes)
        $buffer->writeUnsignedInt(0); // reserved (4 bytes);
        $buffer->writeUnsignedShort($this->getDeviceType()); // device type (2 bytes)
        $buffer->writeUnsignedShort($this->getDeviceId()); // device id (2 bytes)
        $buffer->writeUnsignedInt($this->getTimestamp()); // timestamp (4 bytes)
        $buffer->write(hex2bin($this->secret)); // checksum (16 bytes)
        $buffer->write($encryptedData); // data

        $bytes = $buffer->getData();
        $checksum = md5($bytes, true);

        // Replace bytes with calculated checksum
        $bytes = substr_replace($bytes, $checksum, 16, 16);

        return $bytes;
    }

    public static function fromBuffer(ByteBuffer $buffer, string $secret): self
    {
        if (bin2hex($buffer->read(2)) !== '2131') {
            throw new \RuntimeException('Bad packet');
        }

        $length = $buffer->readUnsignedShort();
        $buffer->readUnsignedInt();
        $deviceType = $buffer->readUnsignedShort();
        $deviceId = $buffer->readUnsignedShort();
        $timestamp = $buffer->readUnsignedInt();
        $checksum = bin2hex($buffer->read(16));
        $data = $buffer->read($length - 32);

        if ($data) {
            $key = md5(hex2bin($secret), true);
            $iv = md5($key . hex2bin($secret), true);
            $decryptedData = openssl_decrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        } else {
            $decryptedData = '';
        }

        $buffer->flush();

        return new self(
            $deviceType,
            $deviceId,
            $timestamp,
            $decryptedData,
            $secret
        );
    }
}