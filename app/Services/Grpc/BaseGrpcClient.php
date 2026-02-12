+<?php

namespace App\Services\Grpc;

use Grpc\ChannelCredentials;
use RuntimeException;

abstract class BaseGrpcClient
{
    protected array $options;

    public function __construct(protected string $target, array $options = [])
    {
        $this->options = array_merge([
            'credentials' => ChannelCredentials::createInsecure(),
        ], $options);
    }

    protected function unwrap($call)
    {
        [$response, $status] = $call->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new RuntimeException('gRPC call failed: '.$status->details, $status->code);
        }

        return $response;
    }
}