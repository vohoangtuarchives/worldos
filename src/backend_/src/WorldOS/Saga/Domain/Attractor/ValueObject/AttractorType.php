<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Attractor\ValueObject;

/**
 * AttractorType — The emergent narrative endings defined as systemic attractors.
 * Endings are not pre-written branches; they are gravitational endpoints the simulation state falls into.
 */
enum AttractorType: string
{
    /** 
     * Hero bends the cosmos to their will. 
     * Triggered by: Low Universe Stability + Extreme Hero Conviction + Breakthrough Phase.
     * Tone: Creation, Subversion, Transcendence.
     */
    case REWRITE = 'rewrite';

    /** 
     * Hero harmonizes perfectly with the system. 
     * Triggered by: High Universe Stability + Low Hero Stress + Restabilization Phase.
     * Tone: Acceptance, Greatness, Unity.
     */
    case CONVERGENCE = 'convergence';

    /** 
     * Hero rejects the universe and breaks out.
     * Triggered by: High Universe Tension + High Hero Conviction + Suppressed Dominant Dimension.
     * Tone: Freedom, Exile, Beyond.
     */
    case ESCAPE = 'escape';

    /** 
     * The universe crushes the Hero or destroys itself.
     * Triggered by: Critical Universe Entropy + Saturated Hero Stress + Collapse Phase.
     * Tone: Tragedy, Void, Inevitability.
     */
    case COLLAPSE = 'collapse';
}
