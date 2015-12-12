<?php

/**
 * Milkyway Multimedia
 * DependantMembershipCheckoutComponent.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\Shop\CheckoutExtras\Contracts\CheckoutComponent_HasConstraints as HasConstraints;
use Milkyway\SS\ZenForms\Constraints\RequiredIf as RequiredIf;

class DependantMembershipCheckoutComponent extends MembershipCheckoutComponent implements HasConstraints
{
    protected $checked;

    public function __construct($confirmed = true, $validator = null, $checked = true)
    {
        $this->checked = $checked;
        parent::__construct($confirmed, $validator);
    }

    public function getFormFields(Order $order)
    {
        $fields = parent::getFormFields($order);

        if(!$fields->exists()) {
            return $fields;
        }

        $checkbox = CheckboxField::create('RegisterAnAccount',
            _t('DependantMembershipCheckoutComponent.REGISTER_ACCOUNT', 'Register an account'), $this->checked);

        return FieldList::create(
            $checkbox,
            CompositeField::create(
                parent::getFormFields($order)
            )
                ->setName('RegisterAccount-Holder')
                ->setAttribute('data-show-if', '[name=' . get_class($this) . '_RegisterAnAccount]:checked')
        );
    }

    public function getRequiredFields(Order $order)
    {
        return [];
    }

    public function validateData(Order $order, array $data)
    {
        if (isset($data['RegisterAnAccount']) && $data['RegisterAnAccount']) {
            return parent::validateData($order, $data);
        }

        return ValidationResult::create();
    }

    public function setData(Order $order, array $data)
    {
        if (isset($data['RegisterAnAccount']) && $data['RegisterAnAccount']) {
            parent::setData($order, $data);
        }
    }

    public function getConstraints(Order $order, $form = null)
    {
        $required = $this->getRequiredFields($order);
        $constraints = [];
        $namespace = $this->name();

        foreach ($required as $requirement) {
            $constraints[$namespace . '_' . $requirement] = new RequiredIf($namespace . '_RegisterAnAccount',
                'not(:checked)');
        }

        return $constraints;
    }

    public function setChecked($flag = true)
    {
        $this->checked = $flag;

        return $this;
    }
} 