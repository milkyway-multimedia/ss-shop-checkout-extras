<?php

/**
 * Milkyway Multimedia
 * DependantBillingAddressCheckoutComponent.php
 *
 * @package reggardocolaianni.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
abstract class DependantAddressCheckoutComponent extends AddressCheckoutComponent implements \Milkyway\Shop\CheckoutExtras\Contracts\CheckoutComponent_HasConstraints
{
    public function getFormFields(Order $order)
    {
        $field = $this->addresstype . 'ToSameAddress';
        $checkbox = CheckboxField::create($field, _t('Dependant' . $this->addresstype . 'AddressCheckoutComponent.SAME_AS_' . $this->useAddresstype, 'Use ' . $this->useAddresstype . ' address'), true);

        return FieldList::create(
            $checkbox,
            CompositeField::create(
                parent::getFormFields($order)
            )
                ->setName($this->addresstype . 'Address')
                ->setAttribute('data-hide-if', '[name=' . get_class($this) . '_' . $field . ']:checked')
        );
    }

    public function getRequiredFields(Order $order) {
        return array();
    }

    public function setData(Order $order, array $data) {
        if(!isset($data[$this->addresstype . 'ToSameAddress'])) {
            parent::setData($order, $data);
        }
        else {
            $order->{$this->addresstype."AddressID"} = $order->{$this->useAddresstype."AddressID"};

            if(!$order->BillingAddressID)
                $order->BillingAddressID = $order->{$this->useAddresstype."AddressID"};
        }

        // FIX for missing name fields
        $fields = array_intersect_key($data, array_flip(['FirstName', 'Surname', 'Email']));
        $changed = false;
        foreach($fields as $field => $value) {
            if(!$order->{$this->addresstype."Address"}->$field)
            {
                $order->{$this->addresstype . "Address"}->$field = $value;
                $changed = true;
            }
        }

        if($changed)
            $order->{$this->addresstype."Address"}->write();
    }

    public function getConstraints(Order $order) {
        $required = parent::getRequiredFields($order);
        $constraints = array();
        $namespace = get_class($this);

        foreach($required as $requirement)
            $constraints[$namespace . '_' . $requirement] = new Milkyway\ZenForms\Constraints\RequiredIf($namespace . '_' . $this->addresstype . 'ToSameAddress', 'not(:checked)');

        return $constraints;
    }
}

class DependantShippingAddressCheckoutComponent extends DependantAddressCheckoutComponent {

    protected $addresstype = "Shipping";
    protected $useAddresstype = "Billing";

    protected $dependson = array(
        'CustomerDetailsCheckoutComponent',
        'BillingAddressCheckoutComponent',
    );

}

class DependantBillingAddressCheckoutComponent extends DependantAddressCheckoutComponent {

    protected $addresstype = "Billing";
    protected $useAddresstype = "Shipping";

    protected $dependson = array(
        'CustomerDetailsCheckoutComponent',
        'ShippingAddressCheckoutComponent',
    );
}