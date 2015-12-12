<?php namespace Milkyway\SS\Shop\CheckoutExtras\Contracts;

/**
 * Milkyway Multimedia
 * ConditionalRequiredFields.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Order;

interface CheckoutComponent_HasConstraints {
    public function getConstraints(Order $order, $form = null);
} 