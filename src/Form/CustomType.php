<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;

class CustomType extends AbstractType {
    /**
     * Génère la configuration de base d'un champ.
     * 
     * @param string $label
     * @param string $placeholder
     * @param bool $required
     * @param array $options
     * @return array
     */
    protected function getConfig($label, $placeholder, bool $required = false, $options = []) {
        return
            array_merge_recursive(
                [
                    'label' => $label,
                    'attr' => [
                        'placeholder' => $placeholder
                    ]
                ], 
                $options,
                $required == false ? [ 'required' => false ] : []
            );
    }
}