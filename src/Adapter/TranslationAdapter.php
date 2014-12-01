<?php

namespace Partnermarketing\TranslationBundle\Adapter;

abstract class TranslationAdapter
{
    /**
     * Push the base translations to OneSky.
     */
    abstract public function pushBaseTranslations();

    /**
     * Get the list of phrase collection keys.
     *
     * @return string[]
     */
    abstract public function listPhraseCollections();

    /**
     * Get a phrase collection from OneSky.
     *
     * @param  string $phraseCollectionKey
     * @return array
     */
    abstract public function getPhraseCollection($phraseCollectionKey);

    /**
     * Check whether a phrase collection exists with the specified key.
     *
     * @param  string  $phraseCollectionKey
     * @return boolean
     */
    abstract public function isPhraseCollection($phraseCollectionKey);

    abstract public function dumpPhraseCollectionToYamlFile($phraseCollectionKey);

    abstract public function dumpAllPhraseCollectionsToYamlFiles();
}
