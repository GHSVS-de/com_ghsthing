<?php
// This is a special file that tells Joomla! how to initialize the component - which services it requires and how they should be provided.

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;

// HTMLHelpers.
use Joomla\CMS\HTML\Registry;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;

use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

use GHSVS\Component\GhsThing\Administrator\Extension\GhsthingComponent;
// Hier nicht benötigt, da obige die MVCComponent extended.
// use Joomla\CMS\Extension\MVCComponent;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
	public function register(Container $container)
	{

		$app = Factory::getApplication();
		$wa = $app->getDocument()->getWebAssetManager();
		$wa->getRegistry()->addExtensionRegistryFile('com_ghsthing');


		$ns = '\\GHSVS\\Component\\GhsThing';

		$container->registerServiceProvider(new CategoryFactory($ns));

		$container->registerServiceProvider(new MVCFactory($ns));

		$container->registerServiceProvider(new ComponentDispatcherFactory($ns));

		$container->registerServiceProvider(new RouterFactory($ns));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				// Diese extended MVCComponent.
				$component = new GhsthingComponent(
					$container->get(ComponentDispatcherFactoryInterface::class)
				);

				// HTMLHelper-Methoden. Siehe auch HTMLHelper::getServiceRegistry()->register( in Venobox-Plugin.
				$component->setRegistry($container->get(Registry::class));

				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));


				return $component;
			}
		);
	}
};
