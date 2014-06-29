<?php /**
 * Milkyway Multimedia
 * DependantMembershipCheckoutComponent.php
 *
 * @package reggardocolaianni.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class DependantMembershipCheckoutComponent extends MembershipCheckoutComponent implements HasRequireIfFields {
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

    public function getRequiredIf(Order $order) {
//        $required = parent::getRequiredFields($order);
        $required = array('Password');
        $requiredIf = array();
        $namespace = get_class($this);

        foreach($required as $requirement)
            $requiredIf[$namespace . '_' . $requirement] = $namespace . '_RegisterAnAccount:checked';

        return $requiredIf;
    }

    public function setChecked($flag = true) {
        $this->checked = $flag;
        return $this;
    }
} 