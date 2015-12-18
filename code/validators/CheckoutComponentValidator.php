<?php namespace Milkyway\SS\Shop\CheckoutExtras\Validators;

/**
 * Milkyway Multimedia
 * CheckoutComponentValidator.php
 *
 * @package milkyway-multimedia/ss-shop-checkout-extras
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use ZenValidator;
use CheckoutComponentConfig;
use BaseCheckoutComponentConfig;

class CheckoutComponentValidator extends ZenValidator
{
    protected $config;
    protected $original;
    protected $originalClass = 'CheckoutComponentValidator';

    protected $hasOriginalConstraints = false;

    public function __construct(CheckoutComponentConfig $config, $constraints = [], $parsleyEnabled = true, $defaultJS = null)
    {
        $this->config = $config;

        parent::__construct($constraints, $parsleyEnabled, $defaultJS);

        $this->addRequiredFields($this->config->getRequiredFields());
    }

    public function setForm($form)
    {
        $return = parent::setForm($form);

        if (!$this->hasOriginalConstraints) {
            if ($this->config instanceof BaseCheckoutComponentConfig) {
                $this->setConstraints($this->config->getConstraints($this->form));
            }

            $this->hasOriginalConstraints = true;
        }

        return $return;
    }

    public function php($data)
    {
        return parent::php($data) && $this->original()->{__FUNCTION__}($data);
    }

    public function fieldHasError($field)
    {
        return $this->original()->{__FUNCTION__}($field);
    }

    protected function original()
    {
        if (!$this->original) {
            $this->original = new $this->originalClass($this->config);
        }

        $this->original->setForm($this->form);

        return $this->original;
    }
}
