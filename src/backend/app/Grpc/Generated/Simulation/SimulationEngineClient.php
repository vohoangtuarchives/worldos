<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Simulation;

/**
 */
class SimulationEngineClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Run exactly one tick iteratively
     * @param \Simulation\TickRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function RunTick(\Simulation\TickRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/simulation.SimulationEngine/RunTick',
        $argument,
        ['\Simulation\TickResponse', 'decode'],
        $metadata, $options);
    }

}
