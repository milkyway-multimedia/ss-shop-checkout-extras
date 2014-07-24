<?php namespace Milkyway\SS\Shop\CheckoutExtras\Validators;
/**
 * Milkyway Multimedia
 * CheckoutComponentValidator.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
class CheckoutComponentValidator extends \ZenValidator {
    protected $config;
    protected $original;
    protected $originalClass = '\CheckoutComponentValidator';

    public function __construct(\CheckoutComponentConfig $config, $constraints = [], $parsleyEnabled = true, $defaultJS = null) {
        $this->config = $config;

        if($config instanceof \BaseCheckoutComponentConfig)
            $constraints = array_merge($config->getConstraints(), $constraints);

        parent::__construct($constraints, $parsleyEnabled, $defaultJS);

        $this->addRequiredFields($this->config->getRequiredFields());
    }

    public function php($data) {
        return parent::php($data) && $this->original()->php($data);
    }

    public function fieldHasError($field) {
        return $this->original()->fieldHasError($field);
    }

    protected function original() {
        if(!$this->original)
            $this->original = \Object::create($this->originalClass, $this->config);

        $this->original->setForm($this->form);

        return $this->original;
    }
} 