<?php namespace Milkyway\SS\Shop\CheckoutExtras\Checkout;

/**
 * Milkyway Multimedia
 * PaymentForm.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use PaymentForm as Original;
use CheckoutComponentConfig;
use CheckoutComponentValidator;

class PaymentForm extends Original
{
    public function __construct($controller, $name, CheckoutComponentConfig $config)
    {
        parent::__construct($controller, $name, $config);

        if ($this->Validator && $this->Validator instanceof CheckoutComponentValidator) {
            $this->Validator = CheckoutComponentValidator::create($this->config)->setForm($this);
        }
    }

    public function FormName()
    {
        $class = $this->class;
        $this->class = 'PaymentForm';
        $name = parent::FormName();
        $this->class = $class;

        return $name;
    }
}
