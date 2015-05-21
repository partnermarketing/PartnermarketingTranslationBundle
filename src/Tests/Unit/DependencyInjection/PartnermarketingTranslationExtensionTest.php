<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Partnermarketing\TranslationBundle\DependencyInjection\PartnermarketingTranslationExtension;

class PartnermarketingTranslationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PartnermarketingTranslationExtension
     */
    private $extension;

    /**
     * Root name of the configuration
     *
     * @var string
     */
    private $root;

    public function setUp()
    {
        parent::setUp();

        $this->extension = $this->getExtension();
        $this->root      = "partnermarketing_translation";
    }

    public function testGetConfigParamsExist()
    {
        $configs = array(
            "supported_languages" => null,
            "one_sky"             => array(
                "project_id" => "123",
                "api_key"    => "xxx",
                "api_secret" => "xxx"
            )

        );

        $this->extension->load( array( $configs ), $container = $this->getContainer() );

        $this->assertTrue($container->hasParameter($this->root . ".supported_languages"));
        $this->assertTrue($container->hasParameter($this->root . ".one_sky.project_id"));
        $this->assertTrue($container->hasParameter($this->root . ".one_sky.api_key"));
        $this->assertTrue($container->hasParameter($this->root . ".one_sky.api_secret"));
    }


    public function testGetConfigWithOverrideValues()
    {
        $configs = array(
                "supported_languages" => null,
                "one_sky" => array(
                    "project_id" => "123",
                    "api_key" => "xxx",
                    "api_secret" => "xxx"
                )

        );

        $this->extension->load(array($configs), $container = $this->getContainer());

        $this->assertEquals(null, $container->getParameter($this->root . ".supported_languages"));
        $this->assertEquals('123', $container->getParameter($this->root . ".one_sky.project_id"));
        $this->assertEquals('xxx', $container->getParameter($this->root . ".one_sky.api_key"));
        $this->assertEquals('xxx', $container->getParameter($this->root . ".one_sky.api_secret"));
    }

    /**
     * @return PartnermarketingTranslationExtension
     */
    protected function getExtension()
    {
        return new PartnermarketingTranslationExtension();
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        $container = new ContainerBuilder();

        return $container;
    }
}
