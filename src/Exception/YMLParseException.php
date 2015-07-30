<?php

namespace Partnermarketing\TranslationBundle\Exception;

use Symfony\Component\Yaml\Exception\ParseException;

class YMLParseException extends ParseException
{
    /**
     * @var string
     */
    private $ymlFile;

    public function __construct(ParseException $previous, $yamlFile)
    {
        $message = 'Failed to parse file: \''.$yamlFile.'\' on line '.$previous->getParsedLine().'.';
        parent::__construct($message, $previous->getCode(), $previous);
        $this->setYmlFile($yamlFile);
    }

    /**
     * @return string
     */
    public function getYmlFile()
    {
        return $this->ymlFile;
    }

    /**
     * @param string $ymlFile
     */
    public function setYmlFile( $ymlFile )
    {
        $this->ymlFile = $ymlFile;
    }

}
