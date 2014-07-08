<?php namespace Milkyway\Shop\CheckoutExtras\Contracts;
/**
 * Milkyway Multimedia
 * ConditionalRequiredFields.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
interface CheckoutComponent_HasConstraints {
    public function getConstraints(\Order $order);
} 