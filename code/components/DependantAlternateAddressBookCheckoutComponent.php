<?php

/**
 * Milkyway Multimedia
 * DependantAlternateAddressBookCheckoutComponent.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\ZenForms\Constraints\Multiple;
use Milkyway\SS\ZenForms\Constraints\RequiredIfStrict;

abstract class DependantAlternateAddressBookCheckoutComponent extends AlternateAddressBookCheckoutComponent
{
    protected $useAddressType;

    public function getFormFields(Order $order)
    {
        $fields = parent::getFormFields($order);
        $field = $this->addresstype . 'ToSameAddress';
        $checkbox = CheckboxField::create($field,
            _t('Dependant' . $this->addresstype . 'AddressCheckoutComponent.SAME_AS_' . $this->useAddressType,
                'Use ' . $this->useAddressType . ' address'), true)
            ->setRightTitle(_t('AddressCheckoutComponent.ADDRESS-' . $this->useAddressType, 'Billing Address'));

        if ($use = $fields->fieldByName('Use')) {
            $use->setTitle(_t('Dependant' . $this->addresstype . 'AddressCheckoutComponent.USE', 'Use'));
        }

        return FieldList::create(
            $checkbox,
            CompositeField::create(
                $fields
            )
                ->setName($this->addresstype . 'Address')
                ->setAttribute('data-hide-if', '[name=' . $this->name() . '_' . $field . ']:checked')
        );
    }

    public function getRequiredFields(Order $order)
    {
        // Relies on constraints
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
            if (!$order->{$this->addresstype . "Address"}()->$field) {
                $order->{$this->addresstype . "Address"}()->$field = $value;
                $changed = true;
            }
        }

        if ($country = ShopConfig::current()->SingleCountry) {
            $order->{$this->addresstype . "Address"}()->Country = $country;
        }

        if ($changed || $order->{$this->addresstype . "Address"}()->isChanged()) {
            $order->{$this->addresstype . "Address"}()->write();
        }
    }

    public function getConstraints(Order $order, $form = null)
    {
        $constraints = parent::getConstraints($order, $form);

        $sameField = $this->name() . '_' . $this->addresstype . 'ToSameAddress';

        if (empty($constraints)) {
            $required = parent::getRequiredFields($order);
            $namespace = $this->name();

            foreach ($required as $requirement) {
                $constraints[$namespace . '_' . $requirement] = new RequiredIfStrict($namespace . '_' . $this->addresstype . 'ToSameAddress',
                    'not(:checked)');
            }
        }

        foreach ($constraints as $field => $constraint) {
            $constraints[$field] = Multiple::create([
                $field => [
                    RequiredIfStrict::create($sameField, 'not(:checked)'),
                    $constraint,
                ],
            ]);
        }

        return $constraints;
    }
}

class DependantAlternateAddressBookCheckoutComponent_Shipping extends DependantAlternateAddressBookCheckoutComponent
{

    protected $addresstype = 'Shipping';
    protected $useAddressType = 'Billing';

    protected $dependson = [
        'CustomerDetailsCheckoutComponent',
        'AlternateAddressBookCheckoutComponent_Shipping',
    ];
}

class DependantAlternateAddressBookCheckoutComponent_Billing extends DependantAlternateAddressBookCheckoutComponent
{

    protected $addresstype = 'Billing';
    protected $useAddressType = 'Shipping';

    protected $dependson = [
        'CustomerDetailsCheckoutComponent',
        'AlternateAddressBookCheckoutComponent_Billing',
    ];
}
