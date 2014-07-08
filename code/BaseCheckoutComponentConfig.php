<?php
/**
 * Milkyway Multimedia
 * Milkyway_SinglePageCheckoutComponentConfig.php
 *
 * @package reggardocolaianni.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

class BaseCheckoutComponentConfig extends SinglePageCheckoutComponentConfig {
    public function getNamespacedComponentByType($type) {
        foreach($this->components as $component) {
            if($this->namespaced){
                if(get_class($component->Proxy()) == $type) return $component;
            }else{
                if($component instanceof $type) return $component;
            }
        }
    }

    /**
     * @param CheckoutComponent $component
     * @param string $insertBefore The class of the component to insert this one before
     */
    public function addComponent(CheckoutComponent $component, $insertBefore = null) {
        if($this->namespaced) {
            $component = new CheckoutComponent_Namespaced($component);
        }

        if($insertBefore) {
            $existingItems = $this->getComponents();
            $this->components = new ArrayList;
            $inserted = false;
            foreach($existingItems as $existingItem) {
                if($existingItem instanceof CheckoutComponent_Namespaced)
                    $existingItemToCheck = $existingItem->Proxy();
                else
                    $existingItemToCheck = $existingItem;

                if(!$inserted && $existingItemToCheck instanceof $insertBefore) {
                    $this->components->push($component);
                    $inserted = true;
                }
                $this->components->push($existingItem);
            }
            if(!$inserted) $this->components->push($component);
        } else {
            $this->getComponents()->push($component);
        }
        return $this;
    }

    public function getConstraints() {
        $constraints = array();

        foreach($this->getComponents() as $component) {
            if($component instanceof CheckoutComponent_Namespaced)
                $component = $component->Proxy();

            if($component instanceof \Milkyway\Shop\CheckoutExtras\Contracts\CheckoutComponent_HasConstraints)
                $constraints = array_merge($constraints, $component->getConstraints($this->order));
        }

        return $constraints;
    }
} 