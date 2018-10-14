<?php
/**
 * Created by PhpStorm.
 * User: vicha
 * Date: 11/10/2018
 * Time: 19:22
 */

namespace Nuttilea\Middleware;

use Closure;

interface LayerInterface {
    
    public function handle( $object, Closure $next );
}
