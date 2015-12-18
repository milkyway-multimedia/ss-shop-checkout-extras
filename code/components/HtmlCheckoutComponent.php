<?php

/**
 * Milkyway Multimedia
 * HtmlCheckoutComponent.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

class HtmlCheckoutComponent extends CheckoutComponent
{
    public $content = '';

    protected $name;

    public function __construct($name, $content = '')
    {
        $this->name = $name;
        parent::__construct();
    }

    public function getFormFields(Order $order)
    {
        return FieldList::create(
            LiteralField::create('Content', $this->content)
        );
    }

    public function validateData(Order $order, array $data)
    {
    }

    public function getData(Order $order)
    {
        return [];
    }

    public function setData(Order $order, array $data)
    {
    }

    public function name()
    {
        return $this->name;
    }
}
