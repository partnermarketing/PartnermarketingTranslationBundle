<?php

namespace Partnermarketing\TranslationBundle\Utilities;

trait HasUtilitiesTrait {

    /**
     * @param array $input
     */
    function ksortMultiDimensional(array $input) {
        ksort($input);

        foreach ($input as $key => &$value) {
            if (is_array($value) && isset($value[0])) {
                $input[$key] = $value[0];
            } elseif (is_array($value)) {
                $this->ksortMultiDimensional($value);
                $input[$key] = $value;
            } else {
                $input[$key] = $value;
            }
        }
    }
}