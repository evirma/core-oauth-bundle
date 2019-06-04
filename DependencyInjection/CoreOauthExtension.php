<?php

namespace Meniam\Bundle\CoreOauthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CoreOauthExtension extends Extension
{
    /**
     * @param  array            $configs
     * @param  ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->enableServices($config['services'], $config, $container);

        $definition = new Definition('GuzzleHttp\\Client');
        $definition->addArgument([ 'timeout' => 10 ]);
        $container->setDefinition('core_oauth.oauth.guzzle', $definition);

        $definition = new Definition('Meniam\Bundle\CoreOauthBundle\Service\OAuthService');
        $definition->addMethodCall('setContainer', [ new Reference('service_container') ]);
        $container->setDefinition('core_oauth.oauth', $definition);

        $definition = new Definition('Meniam\Bundle\CoreOauthBundle\OAuth\RequestDataStorage\SessionStorage');
        $definition->addArgument(new Reference('session'));
        $container->setDefinition('core_oauth.oauth.storage.session', $definition);
    }

    private function enableServices($config, $globalConfig, ContainerBuilder $container)
    {
        foreach ($config as $id => $serviceConfig) {
            $className = 'Meniam\\Bundle\\CoreOauthBundle\\OAuth\\ResourceOwner\\' . ucfirst($serviceConfig['resource_owner']) . 'ResourceOwner';

            $definition = new Definition($className);
            $definition->addArgument(new Reference('core_oauth.oauth.guzzle'));
            $definition->addArgument(new Reference('security.http_utils'));
            $definition->addArgument($serviceConfig['options']);
            $definition->addArgument($serviceConfig['resource_owner']);
            $definition->addArgument(new Reference('core_oauth.oauth.storage.session'));
            $container->setDefinition('core_oauth.oauth.service.' . $id . '.resource_owner', $definition);

            $definition = new Definition('Meniam\\Bundle\\CoreOauthBundle\\Service');
            $definition->addArgument($id);
            $definition->addArgument($serviceConfig['title']);
            $definition->addArgument(new Reference('core_oauth.oauth.service.' . $id . '.resource_owner'));
            $definition->addMethodCall('setUrlGenerator', [ new Reference('router') ]);
            $definition->addMethodCall('setRedirectUriRoute', [ $globalConfig['redirect_uri_route'] ]);
            $container->setDefinition('core_oauth.oauth.service.' . $id, $definition);
        }
    }
}
