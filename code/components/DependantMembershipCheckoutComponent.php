<?php /**
 * Milkyway Multimedia
 * DependantMembershipCheckoutComponent.php
 *
 * @package reggardocolaianni.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class DependantMembershipCheckoutComponent extends MembershipCheckoutComponent implements \Milkyway\SS\Shop\CheckoutExtras\Contracts\CheckoutComponent_HasConstraints {
    protected $checked;

    public function __construct($confirmed = true, $validator = null, $checked = true) {
        $this->checked = $checked;
        parent::__construct($confirmed, $validator);
    }

    public function getFormFields(Order $order)
    {
        $checkbox = CheckboxField::create('RegisterAnAccount', _t('DependantMembershipCheckoutComponent.REGISTER_ACCOUNT', 'Register an account'), $this->checked);

        return FieldList::create(
            $checkbox,
            CompositeField::create(
                parent::getFormFields($order)
            )
                ->setName('RegisterAccount-Holder')
                ->setAttribute('data-show-if', '[name=' . get_class($this) . '_RegisterAnAccount]:checked')
        );
    }

    public function getRequiredFields(Order $order) {
        return array();
    }

    public function validateData(Order $order, array $data) {
        if(Member::currentUserID()){
            return;
        }

        $result = new ValidationResult();

        if(isset($data['RegisterAnAccount']) && !isset($data['Password'])) {
            $result->error(_t('DependantMembershipCheckoutComponent.PASSWORD_REQUIRED', 'A password is required to register an account'));
            throw new ValidationException($result);
        }

        parent::validateData($order, $data);
    }

    public function setData(Order $order, array $data) {
        if(isset($data['RegisterAnAccount'])) {
            parent::setData($order, $data);
        }
    }

    public function getConstraints(Order $order) {
        $required = parent::getRequiredFields($order);
        $constraints = array();
        $namespace = get_class($this);

        foreach($required as $requirement)
            $constraints[$namespace . '_' . $requirement] = new Milkyway\SS\ZenForms\Constraints\RequiredIf($namespace . '_' . $this->addresstype . 'ToSameAddress', 'not(:checked)');

        return $constraints;
    }

    public function setChecked($flag = true) {
        $this->checked = $flag;
        return $this;
    }
} 