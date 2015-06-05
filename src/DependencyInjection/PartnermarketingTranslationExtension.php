<?php

namespace Partnermarketing\TranslationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PartnermarketingTranslationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('partnermarketing_translation.base_language', $config['base_language']);
        $container->setParameter('partnermarketing_translation.supported_languages', $config['supported_languages']);
        $container->setParameter('partnermarketing_translation.one_sky.project_id', $config['one_sky']['project_id']);
        $container->setParameter('partnermarketing_translation.one_sky.api_key', $config['one_sky']['api_key']);
        $container->setParameter('partnermarketing_translation.one_sky.api_secret', $config['one_sky']['api_secret']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
