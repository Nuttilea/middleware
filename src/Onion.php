<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 10/9/18
 * Time: 2:51 PM
 */

namespace Nuttilea\Middleware;

use Closure;
use InvalidArgumentException;

class Onion {
    
    private $inputLayers;
    private $outputLayers;
    
    public function __construct( array $inputLayers = [], array $outputLayers = [] ) {
        $this->inputLayers = $inputLayers;
        $this->outputLayers = $outputLayers;
    }
    
    /**
     * Add layer(s) or Onion
     *
     * @param  mixed $inputLayers
     *
     * @return Onion
     */
    public function addInputLayer( $inputLayers ) {
        if ($inputLayers instanceof Onion) {
            $inputLayers = $inputLayers->toArray();
        }
        if ($inputLayers instanceof LayerInterface) {
            $inputLayers = [$inputLayers];
        }
        if (!is_array($inputLayers)) {
            throw new InvalidArgumentException(get_class($inputLayers)." is not a valid onion layer.");
        }
        
        return new static(array_merge($this->inputLayers, $inputLayers));
    }
    
    /**
     * Add layer(s) or Onion
     *
     * @param  mixed $outputLayers
     *
     * @return Onion
     */
    public function addOutputLayer( $outputLayers ) {
        if ($outputLayers instanceof Onion) {
            $outputLayers = $outputLayers->toArray();
        }
        if ($outputLayers instanceof LayerInterface) {
            $outputLayers = [$outputLayers];
        }
        if (!is_array($outputLayers)) {
            throw new InvalidArgumentException(get_class($outputLayers)." is not a valid onion layer.");
        }
        
        return new static(array_merge($this->inputLayers, $outputLayers));
    }
    
    /**
     * Run middleware around core function and pass an
     * object through it
     *
     * @param  mixed   $object
     * @param  Closure $core
     *
     * @return mixed
     */
    public function handle( $object, Closure $core ) {
        $coreFunction = $this->createCoreFunction($core);
        $inputLayer = array_reverse($this->inputLayers);
        $outputLayers = array_reverse($this->outputLayers);
        $completeOnion = array_reduce($inputLayer, function( $nextLayer, $layer ) {
            return $this->createLayer($nextLayer, $layer);
        }, $coreFunction);
        $response = $completeOnion($object);
        $completeOnion = array_reduce($outputLayers, function( $nextLayer, $layer ) {
            return $this->createLayer($nextLayer, $layer);
        }, $this->createCoreFunction(function( $object ) { return $object; }));
        
        return $completeOnion($response);
    }
    
    /**
     * Get the layers of this onion, can be used to merge with another onion
     * @return array
     */
    public function toArray() {
        return array_merge($this->inputLayers, $this->outputLayers);
    }
    
    /**
     * The inner function of the onion.
     * This function will be wrapped on layers
     *
     * @param  Closure $core the core function
     *
     * @return Closure
     */
    private function createCoreFunction( Closure $core ) {
        return function( $object ) use ( $core ) {
            return $core($object);
        };
    }
    
    /**
     * Get an onion layer function.
     * This function will get the object from a previous layer and pass it inwards
     *
     * @param  LayerInterface $nextLayer
     * @param  LayerInterface $layer
     *
     * @return Closure
     */
    private function createLayer( $nextLayer, $layer ) {
        return function( $object ) use ( $nextLayer, $layer ) {
            ;
            
            return $layer->handle($object, $nextLayer);
        };
    }
}
