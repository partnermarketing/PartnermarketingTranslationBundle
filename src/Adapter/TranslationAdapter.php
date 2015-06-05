<?php

namespace Partnermarketing\TranslationBundle\Adapter;

abstract class TranslationAdapter
{
    /**
     * Push the base translations to destination adapter service.
     */
    abstract public function pushBaseTranslations();

    /**
     * Fetch all translations from adapter service and dump them all into .yml files.
     */
    abstract public function dumpAllTranslationsToYamlFiles();
}
