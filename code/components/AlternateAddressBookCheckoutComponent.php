<?php

/**
 * Milkyway Multimedia
 * AlternateAddressBookCheckoutComponent.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\Shop\CheckoutExtras\Contracts\CheckoutComponent_HasConstraints as HasConstraints;
use Milkyway\SS\ZenForms\Constraints\RequiredIfStrict;

abstract class AlternateAddressBookCheckoutComponent extends AddressCheckoutComponent implements HasConstraints
{
    public $allowNew = true;
    public $allowSaveAsNew = true;

    protected $addtoaddressbook = true;

    public function getFormFields(Order $order)
    {
        $member = Member::currentUser() ?: $order->Member();

        if (!$member || !$member->exists() || !$member->AddressBook()->exists()) {
            return parent::getFormFields($order);
        }

        $options = [];
        $default = $this->getDefaultAddress($member);
        $params = [
            'addfielddescriptions' => $this->formfielddescriptions,
        ];

        $addressFields = $default->getFrontEndFields($params);

        if ($this->allowSaveAsNew) {
            $addressFields->push(CheckboxField::create('saveAsNew',
                _t('AlternateAddressBookCheckoutComponent.SAVE_AS_NEW', 'Save as new address')));
        }

        $options[] = SelectionGroup_Item::create(
            'address-' . $default->ID,
            HasOneCompositeField::create(
                'address-' . $default->ID,
                $default,
                $addressFields
            ),
            _t('AlternateAddressBookCheckoutComponent.USE_DEFAULT', '{address}', [
                'address' => $default->Title,
            ])
        );

        if ($member->AddressBook()->exclude('ID', $default->ID)->exists()) {
            foreach ($member->AddressBook()->exclude('ID', $default->ID) as $address) {
                $addressFields = $address->getFrontEndFields($params);

                $addressFields->push(CheckboxField::create('saveAsNew',
                    _t('AlternateAddressBookCheckoutComponent.SAVE_AS_NEW', 'Save as new address')));

                if ($member->DefaultShippingAddressID != $address->ID) {
                    $addressFields->push(CheckboxField::create('useAsDefaultShipping',
                        _t('AlternateAddressBookCheckoutComponent.USE_AS_DEFAULT_SHIPPING',
                            'Use as default shipping address')));
                }

                if ($member->DefaultBillingAddressID != $address->ID) {
                    $addressFields->push(CheckboxField::create('useAsDefaultBilling',
                        _t('AlternateAddressBookCheckoutComponent.USE_AS_DEFAULT_BILLING',
                            'Use as default billing address')));
                }


                $options[] = SelectionGroup_Item::create(
                    'address-' . $address->ID,
                    HasOneCompositeField::create(
                        'address-' . $address->ID,
                        $address,
                        $addressFields
                    ),
                    _t('AlternateAddressBookCheckoutComponent.SHIP_TO_SELECTED_ADDRESS', '{address}', [
                        'address' => $address->Title,
                    ])
                );
            }
        }

        if ($this->allowNew) {
            $addressFields = parent::getFormFields($order);

            $addressFields->push(CheckboxField::create('useAsDefaultShipping',
                _t('AlternateAddressBookCheckoutComponent.USE_AS_DEFAULT_SHIPPING',
                    'Use as default shipping address')));
            $addressFields->push(CheckboxField::create('useAsDefaultBilling',
                _t('AlternateAddressBookCheckoutComponent.USE_AS_DEFAULT_BILLING', 'Use as default billing address')));

            $options[] = SelectionGroup_Item::create(
                'new',
                HasOneCompositeField::create(
                    'new',
                    Address::create(),
                    $addressFields
                ),
                _t('AlternateAddressBookCheckoutComponent.' . $this->addresstype . '_TO_NEW_ADDRESS',
                    'New ' . ucfirst($this->addresstype) . ' Address')
            );
        }

        return FieldList::create(
            TabbedSelectionGroup::create('Use', $options)
                ->setTitle(_t('ShippingDetails.' . $this->addresstype . '_ADDRESS',
                    ucfirst($this->addresstype) . ' Address'))
                ->setLabelTab(true)
                ->showAsDropdown(true)
        );
    }

    public function getRequiredFields(Order $order)
    {
        $member = Member::currentUser() ?: $order->Member();

        if (!$member || !$member->exists() || !$member->AddressBook()->exists()) {
            return parent::getRequiredFields($order);
        }

        // Uses constraints for validation
        return [];
    }

    public function getData(Order $order)
    {
        $member = Member::currentUser() ?: $order->Member();

        if (!$member || !$member->exists() || !$member->AddressBook()->exists()) {
            $data = parent::getData($order);

            if ((!isset($data['Name']) || !$data['Name'])) {
                if ($member = Member::currentUser()) {
                    $data['Name'] = $member->{"Default" . $this->addresstype . "Address"}()->Name ?: $member->Name;
                } else {
                    $data['Name'] = $order->Name;
                }
            }

            return $data;
        }

        $data = [
            'Use' => 'address-' . $this->getDefaultAddress($member)->ID,
        ];

        return $data;
    }

    public function setData(Order $order, array $data)
    {
        if (!isset($data['Use'])) {
            parent::setData($order, $data);

            return;
        }

        $member = Member::currentUser() ?: $order->Member();

        if (strpos($data['Use'], 'address-') === 0) {
            $address = Address::get()->byID(substr($data['Use'], 8));

            if (!$address) {
                throw new ValidationException('That address does not exist');
            }

            if (isset($data[$data['Use']]) && isset($data[$data['Use']]['saveAsNew']) && $data[$data['Use']]['saveAsNew']) {
                if (!$address->canCreate()) {
                    throw new ValidationException('You do not have permission to add a new address');
                }

                $address = $address->duplicate();
            } else {
                if (isset($data[$data['Use']]) && !$address->canEdit() && $address->MemberID != $member->ID) {
                    throw new ValidationException('You do not have permission to use this address');
                }
            }
        } else {
            if (!singleton('Address')->canCreate()) {
                throw new ValidationException('You do not have permission to add a new address');
            }

            $address = Address::create();
        }

        if (isset($data[$data['Use']])) {
            $address->castedUpdate($data[$data['Use']]);

            // FIX for missing name fields
            $fields = array_intersect_key($data, array_flip(['FirstName', 'Surname', 'Name', 'Email']));
            foreach ($fields as $field => $value) {
                if (!$address->$field) {
                    $address->$field = $value;
                }
            }

            if ($country = ShopConfig::current()->SingleCountry) {
                $address->Country = $country;
            }

            if ($this->addtoaddressbook) {
                $address->MemberID = $member->ID;
            }

            $address->write();

            if (isset($data[$data['Use']]['useAsDefaultShipping']) && $data[$data['Use']]['useAsDefaultShipping']) {
                $member->DefaultShippingAddressID = $address->ID;
            }

            if (isset($data[$data['Use']]['useAsDefaultBilling']) && $data[$data['Use']]['useAsDefaultBilling']) {
                $member->DefaultBillingAddressID = $address->ID;
            }

            if ($member->isChanged()) {
                $member->write();
            }
        }

        if ($address->exists()) {
            if ($this->addresstype === 'Shipping') {
                ShopUserInfo::singleton()->setAddress($address);
                Zone::cache_zone_ids($address);
            }

            $order->{$this->addresstype . 'AddressID'} = $address->ID;
            if (!$order->BillingAddressID) {
                $order->BillingAddressID = $address->ID;
            }
            $order->write();
            $order->extend('onSet' . $this->addresstype . 'Address', $address);
        }
    }

    public function getConstraints(Order $order, $form = null)
    {
        $member = Member::currentUser() ?: $order->Member();

        if (!$member || !$member->exists() || !$member->AddressBook()->exists()) {
            return [];
        }

        $required = parent::getRequiredFields($order);
        $constraints = [];
        $namespace = $this->name();

        foreach ($member->AddressBook() as $address) {
            foreach ($required as $requirement) {
                $constraints[$namespace . '_address-' . $address->ID . '[' . $requirement . ']'] =
                    (new RequiredIfStrict($namespace . '_Use', '[value=address-' . $address->ID . ']:checked'))
                        ->setMessage(_t('AlternateAddressBookCheckoutComponent.' . $this->addresstype . '-REQUIRED_IF',
                            'This value is required when ' . strtolower($this->addresstype) . ' to: {address}', [
                                'address' => $address->toString(),
                            ]));
            }
        }


        if ($this->allowNew) {
            foreach ($required as $requirement) {
                $constraints[$namespace . '_new[' . $requirement . ']'] =
                    (new RequiredIfStrict($namespace . '_Use', '[value=new]:checked'))
                        ->setMessage(_t('AlternateAddressBookCheckoutComponent.' . $this->addresstype . '-REQUIRED_IF_NEW',
                            'This value is required when ' . strtolower($this->addresstype) . ' to a new address'));
            }
        }


        return $constraints;
    }

    protected function getDefaultAddress($member)
    {
        return $member->{'Default' . $this->addresstype . 'Address'}()->exists() ? $member->{'Default' . $this->addresstype . 'Address'}() : $member->AddressBook()->first();
    }
}

class AlternateAddressBookCheckoutComponent_Shipping extends AlternateAddressBookCheckoutComponent
{

    protected $addresstype = 'Shipping';
}

class AlternateAddressBookCheckoutComponent_Billing extends AlternateAddressBookCheckoutComponent
{

    protected $addresstype = 'Billing';
}
