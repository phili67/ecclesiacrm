<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\PluginDependenciesQuery as BasePluginDependenciesQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'plugin_dependencies' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class PluginDependenciesQuery extends BasePluginDependenciesQuery
{
	public static function getServiceClassesForPlugin(Plugin $plugin): array
	{
		$dependencies = self::create()
			->filterByPlugin($plugin)
			->filterByExtension('service')
			->find();

		if (is_null($dependencies) || $dependencies->count() === 0) {
			return [];
		}

		$serviceClasses = [];

		foreach ($dependencies as $dependency) {
			$serviceClass = $dependency->getUrl();

			if (empty($serviceClass)) {
				continue;
			}

			$serviceClasses[] = ltrim($serviceClass, '\\');
		}

		return array_values(array_unique($serviceClasses));
	}

	public static function getServiceClassForPlugin(Plugin $plugin): ?string
	{
		$serviceClasses = self::getServiceClassesForPlugin($plugin);

		return $serviceClasses[0] ?? null;
	}

	public static function getServiceInstanceForPlugin(Plugin $plugin): ?object
	{
		$serviceClass = self::getServiceClassForPlugin($plugin);

		if (is_null($serviceClass) || !class_exists($serviceClass)) {
			return null;
		}

		return new $serviceClass();
	}

    public static function getClassServices(Plugin $plugin, string $type = 'service'): array
	{
        $res = [];
        
		foreach (self::getServiceClassesForPlugin($plugin) as $serviceClass) {
			if (!class_exists($serviceClass)) {
				continue;
			}

			$res[] = $serviceClass;			
		}

		return $res;
	}

	public static function isServiceActiveForPlugin(Plugin $plugin): bool
	{
		foreach (self::getServiceClassesForPlugin($plugin) as $serviceClass) {
			if (!class_exists($serviceClass)) {
				continue;
			}

			$service = new $serviceClass();

			if (!method_exists($service, 'isActive')) {
				continue;
			}

			if ((bool)$service->isActive()) {
				return true;
			}
		}

		return false;
	}

}
