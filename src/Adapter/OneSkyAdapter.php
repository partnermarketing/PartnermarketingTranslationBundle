<?php

namespace Partnermarketing\TranslationBundle\Adapter;

use Onesky\Api\Client;
use Onesky\Api\FileFormat;
use Symfony\Component\Yaml\Yaml as YamlParser;

class OneSkyAdapter extends TranslationAdapter
{
    private $baseTranslationsDir;
    private $targetTranslationDir;
    private $oneSkyProjectId;
    private $oneSkyApiKey;
    private $oneSkyApiSecret;
    /** @var Client $client */
    private $client;

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

        $client = $this->getClient();

        if(count($files) > 0) {
            foreach($files as $filePath) {
                $response = $client->files('upload', [
                    'project_id' => $this->oneSkyProjectId,
                    'file' => $filePath,
                    'file_format' => FileFormat::YML
                ]);
            }
        }


        return json_decode($response, true);
    }

    public function getPhraseCollection($phraseCollectionKey)
    {
        $client = $this->getClient();
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
            $languageTag = $this->convertToSymfonyLanguageTag($key);
            $this->dumpToYaml($values, $phraseCollectionKey, $languageTag);
        }
    }


    /**
    +     * This method converts a language code provided by OneSky into Symfony 2 language code format.
    +     * e.g: pt-PT -> pt_PT
    +     *
    +     * View more details about language tag here: http://en.wikipedia.org/wiki/IETF_language_tag
    +     *
    +     * @param $languageString
    +     * @return string
    +     */
    public function convertToSymfonyLanguageTag($languageString)
    {
        $languageParts = explode('-', $languageString);
        $countryCode = $languageParts[0];
        $languageTag = $countryCode;
        if(count($languageParts) > 1) {
            $languageTag .= '_'.$languageParts[1];
        }

        return $languageTag;
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
        return self::getFilesInDirectory($this->baseTranslationsDir);
    }

    private static function getFilesInDirectory($dir)
    {
        $iterator = new \DirectoryIterator($dir);
        $files = [];

        foreach ($iterator as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }

            if (is_dir($file->getPathname())) {
                $files = array_merge($files, self::getFilesInDirectory($file->getPathname()));
            } else {
                $files[] = realpath($file->getPathname());
            }
        }

        sort($files);

        return $files;
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
     * @return Client
     */
    public function getClient(){
        if(!$this->client) {
            $this->client = $this->createClient();
        }
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    private function createClient()
    {
        $client = new Client();
        $client->setApiKey($this->oneSkyApiKey);
        $client->setSecret($this->oneSkyApiSecret);
        return $client;
    }
}
