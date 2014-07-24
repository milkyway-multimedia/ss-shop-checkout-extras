<?php namespace Milkyway\SS\Shop\CheckoutExtras\Checkout;
/**
 * Milkyway Multimedia
 * Controller.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class Controller extends \CheckoutPage_Controller {
    private static $allowed_actions = array(
        'OrderForm',
    );

    public function OrderForm() {
        if(!(bool)$this->Cart()) {
            return false;
        }

        $form = \PaymentForm::create(
            $this,
            'OrderForm',
            \Injector::inst()->create('CheckoutComponentConfig', \ShoppingCart::curr())
        );

        $form->Cart = $this->Cart();
        $this->extend('updateOrderForm', $form);

        return $form;
    }
} 