<?php

namespace Partnermarketing\TranslationBundle\Adapter;

use OneSky\ApiClient;
use Partnermarketing\FileSystemBundle\ServerFileSystem\ServerFileSystem;
use Symfony\Component\Yaml\Yaml as YamlParser;

class OneSkyAdapter extends TranslationAdapter
{
    private $baseTranslationsDir,
        $targetTranslationDir,
        $oneSkyProjectId,
        $oneSkyApiKey,
        $oneSkyApiSecret;

    public function __construct($baseTranslationsDir, $targetTranslationDir, $oneSkyProjectId, $oneSkyApiKey, $oneSkyApiSecret)
    {
        $this->baseTranslationsDir = rtrim($baseTranslationsDir, '/');
        $this->targetTranslationDir = rtrim($targetTranslationDir, '/');
        $this->oneSkyProjectId = $oneSkyProjectId;
        $this->oneSkyApiKey = $oneSkyApiKey;
        $this->oneSkyApiSecret = $oneSkyApiSecret;
    }

    public function pushBaseTranslations()
    {
        $files = $this->getBaseTranslationFiles();
        $phraseCollections = $this->getPhraseCollectionsFromFilenames($files);

        $client = $this->createClient();
        $response = $client->phraseCollections('import', [
            'project_id' => $this->oneSkyProjectId,
            'collections' => $phraseCollections,
        ]);

        return json_decode($response, true);
    }

    public function getPhraseCollection($phraseCollectionKey)
    {
        $client = $this->createClient();
        $response = $client->phraseCollections('show', [
            'project_id' => $this->oneSkyProjectId,
            'collection_key' => $phraseCollectionKey,
        ]);

        return json_decode($response, true);
    }

    public function isPhraseCollection($phraseCollectionKey)
    {
        return in_array($phraseCollectionKey, $this->listPhraseCollections());
    }

    public function listPhraseCollections()
    {
        $files = $this->getBaseTranslationFiles();

        return array_map(function ($file) {
            return $this->getPhraseCollectionKeyFromFilename($file);
        }, $files);
    }

    public function dumpPhraseCollectionToYamlFile($phraseCollectionKey)
    {
        $collection = $this->getPhraseCollection($phraseCollectionKey);

        $english = [];
        foreach ($collection['data']['base_language']['en'] as $key => $value) {
            $english[$key] = $value['string'];
        }
        $this->dumpToYaml($english, $phraseCollectionKey, 'en');

        foreach ($collection['data']['translations'] as $key => $values) {
            $this->dumpToYaml($values, $phraseCollectionKey, $key);
        }
    }

    private function dumpToYaml($phrases, $phraseCollectionKey, $locale)
    {
        $targetFile = $this->getTargetFileFromPhraseCollectionKeyAndLocale($phraseCollectionKey, $locale);
        $targetDir = dirname($targetFile);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $yaml = YamlParser::dump($phrases);
        file_put_contents($targetFile, $yaml);
    }

    public function dumpAllPhraseCollectionsToYamlFiles()
    {
        $phraseCollectionKeys = $this->listPhraseCollections();
        foreach ($phraseCollectionKeys as $phraseCollectionKey) {
            $this->dumpPhraseCollectionToYamlFile($phraseCollectionKey);
        }
    }

    public function getPhraseCollectionsFromFilenames($filenames)
    {
        $collections = [];
        foreach ($filenames as $filename) {
            $key = $this->getPhraseCollectionKeyFromFilename($filename);
            $phrases = $this->getPhrasesFromFilename($filename);
            $collections[$key] = $phrases;
        }

        return $collections;
    }

    public function getPhrasesFromFilename($filename)
    {
        $parsed = YamlParser::parse(file_get_contents($filename));
        $phrases = $this->yamlArrayToDottedPhrasesArray($parsed);

        return $phrases;
    }

    private function yamlArrayToDottedPhrasesArray(array $array, $keyPrefix = null)
    {
        $result = [];

        foreach ($array as $key => $value) {
            $keyToUse = ($keyPrefix ? $keyPrefix . '.' . $key : $key);
            if (is_scalar($value)) {
                $result[$keyToUse] = ['string' => $value];
            } else {
                $result = array_merge($result, $this->yamlArrayToDottedPhrasesArray($value, $keyToUse));
            }
        }

        return $result;
    }

    public function getBaseTranslationFiles()
    {
        return ServerFileSystem::getFilesInDirectory($this->baseTranslationsDir);
    }

    public function getPhraseCollectionKeyFromFilename($filename)
    {
        $key = str_replace($this->baseTranslationsDir, '', $filename);
        $key = ltrim($key, '/');
        $key = preg_replace('/\.yml$/', '', $key);

        return $key;
    }

    public function getTargetFileFromPhraseCollectionKeyAndLocale($phraseCollectionKey, $locale)
    {
        return $this->targetTranslationDir . '/' . $phraseCollectionKey . '.' . $locale . '.yml';
    }

    /**
     * @return \OneSky\ApiClient
     */
    private function createClient()
    {
        return new ApiClient($this->oneSkyApiKey, $this->oneSkyApiSecret);
    }
}
