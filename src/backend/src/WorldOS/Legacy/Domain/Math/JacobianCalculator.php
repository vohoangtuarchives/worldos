<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Math;

use Closure;

/**
 * Calculates the Jacobian matrix numerically using central finite difference.
 */
class JacobianCalculator
{
    private float $epsilon;

    public function __construct(float $epsilon = 1e-5)
    {
        $this->epsilon = $epsilon;
    }

    /**
     * @param Closure $f The evolution function: f(Vector $x): Vector
     * @param Vector $x The state at which to compute the Jacobian
     * @return Matrix The Jacobian Matrix J = dF/dX
     */
    public function compute(Closure $f, Vector $x): Matrix
    {
        $n = $x->dimensionCount();
        $dims = $x->dimensions();
        $jacobianData = [];

        // Base evaluation not needed for central difference, but good to ensure valid output
        $base = $f($x);
        $outDims = $base->dimensions();

        foreach ($dims as $jCol) {
            // Create evaluation points x + epsilon and x - epsilon
            $xPlus = $x->getAll();
            $xPlus[$jCol] += $this->epsilon;
            
            $xMinus = $x->getAll();
            $xMinus[$jCol] -= $this->epsilon;

            $vPlus = new Vector($xPlus);
            $vMinus = new Vector($xMinus);

            $fPlus = $f($vPlus);
            $fMinus = $f($vMinus);

            foreach ($outDims as $iRow) {
                // Central difference: (f(x+h) - f(x-h)) / 2h
                $derivative = ($fPlus->get($iRow) - $fMinus->get($iRow)) / (2 * $this->epsilon);
                $jacobianData[$iRow][$jCol] = $derivative;
            }
        }

        return new Matrix($jacobianData);
    }
}
