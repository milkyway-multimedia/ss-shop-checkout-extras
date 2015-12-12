<?php

/**
 * Milkyway Multimedia
 * DependantAddressCheckoutComponent.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\Shop\CheckoutExtras\Contracts\CheckoutComponent_HasConstraints as HasConstraints;
use Milkyway\SS\ZenForms\Constraints\RequiredIf as RequiredIf;

abstract class DependantAddressCheckoutComponent extends AddressCheckoutComponent implements HasConstraints
{
    protected $useAddressType;

    public function getFormFields(Order $order)
    {
        $field = $this->addresstype . 'ToSameAddress';
        $checkbox = CheckboxField::create($field,
            _t('Dependant' . $this->addresstype . 'AddressCheckoutComponent.SAME_AS_' . $this->useAddressType,
                'Use ' . $this->useAddressType . ' address'), true);

        return FieldList::create(
            $checkbox,
            CompositeField::create(
                parent::getFormFields($order)
            )
                ->setName($this->addresstype . 'Address')
                ->setAttribute('data-hide-if', '[name=' . $this->name() . '_' . $field . ']:checked')
        );
    }

    public function getRequiredFields(Order $order)
    {
        return [];
    }

    public function setData(Order $order, array $data)
    {
        if (!isset($data[$this->addresstype . 'ToSameAddress'])) {
            parent::setData($order, $data);
        } else {
            $order->{$this->addresstype . "AddressID"} = $order->{$this->useAddressType . "AddressID"};

            if (!$order->BillingAddressID) {
                $order->BillingAddressID = $order->{$this->useAddressType . "AddressID"};
            }
        }

        // FIX for missing name fields
        $fields = array_intersect_key($data, array_flip(['FirstName', 'Surname', 'Name', 'Email']));
        $changed = false;
        foreach ($fields as $field => $value) {
            if (!$order->{$this->addresstype . "Address"}->$field) {
                $order->{$this->addresstype . "Address"}->$field = $value;
                $changed = true;
            }
        }

        if ($changed) {
            $order->{$this->addresstype . "Address"}->write();
        }
    }

    public function getConstraints(Order $order, $form = null)
    {
        $required = parent::getRequiredFields($order);
        $constraints = [];
        $namespace = $this->name();

        foreach ($required as $requirement) {
            $constraints[$namespace . '_' . $requirement] = new RequiredIf($namespace . '_' . $this->addresstype . 'ToSameAddress',
                'not(:checked)');
        }

        return $constraints;
    }
}

class DependantShippingAddressCheckoutComponent extends DependantAddressCheckoutComponent
{

    protected $addresstype = "Shipping";
    protected $useAddressType = "Billing";

    protected $dependson = [
        'CustomerDetailsCheckoutComponent',
        'BillingAddressCheckoutComponent',
    ];

}

class DependantBillingAddressCheckoutComponent extends DependantAddressCheckoutComponent
{

    protected $addresstype = "Billing";
    protected $useAddressType = "Shipping";

    protected $dependson = [
        'CustomerDetailsCheckoutComponent',
        'ShippingAddressCheckoutComponent',
    ];
}