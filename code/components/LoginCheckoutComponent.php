<?php

/**
 * Milkyway Multimedia
 * LoginCheckoutComponent.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

class LoginCheckoutComponent extends HtmlCheckoutComponent
{
    public $link;

    public $type = 'alert-info';

    public function __construct($name = '', $content = '', $link = '')
    {
        $name = $name ?: get_called_class();
        parent::__construct($name, $content);
    }

    public function getFormFields(Order $order)
    {
        if (Member::currentUser() || $order->Member()->exists()) {
            return FieldList::create();
        }

        $content = $this->content ?: _t('LoginCheckoutComponent.ALREADY_HAVE_AN_ACCOUNT?', 'Already have an account? <a class="checkout--login-link" href="{{ link }}">Sign in here</a>');
        $link = $this->link ?: singleton('Security')->Link('login');

        return FieldList::create(
            FormMessageField::create('LoginMessage', str_replace('{{ link }}', $link, $content), $this->type)
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
}
