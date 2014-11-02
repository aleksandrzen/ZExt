<?php
/**
 * ZExt Framework (http://z-ext.com)
 * Copyright (C) 2012 Mike.Mirten
 * 
 * LICENSE
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * @copyright (c) 2012, Mike.Mirten
 * @license   http://www.gnu.org/licenses/gpl.html GPL License
 * @category  ZExt
 * @version   1.0
 */

namespace ZExt\Di;

use ZExt\Di\Definition\DefinitionInterface;
use Closure;

/**
 * Dependency injection service container
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Container
 * @author     Mike.Mirten
 * @version    2.0
 */
class Container implements ContainerInterface {
	
	/**
	 * Definitions of services
	 *
	 * @var DefinitionInterface
	 */
	protected $_definitions = [];
	
	/**
	 * Locators
	 *
	 * @var LocatorInterface
	 */
	protected $_locators = [];
	
	/**
	 * Set service definition
	 * 
	 * @param  string $id         ID of service
	 * @param  mixed  $definition Definition of service
	 * @param  mixed  $args       Arguments for constructor of service
	 * @param  bool   $factory    Factory mode: new instance for each request of service
	 * @return DefinitionInterface
	 * @throws Exceptions\ServiceOverride
	 */
	public function set($id, $definition, $args = null, $factory = false) {
		if (isset($this->_definitions[$id])) {
			throw new Exceptions\ServiceOverride('Service "' . $id . '" already exists');
		}
		
		$definition = $this->normalizeDefinition($definition);
		
		if ($args !== null) {
			$definition->setArguments($args);
		}
		
		if ($factory) {
			$definition->setFactoryMode();
		}
		
		$this->_definitions[$id] = $definition;
		
		return $definition;
	}
	
	/**
	 * Set alias for service
	 * 
	 * @param  string $existsId ID of exists service
	 * @param  string $newId    Alias ID
	 * @throws Exceptions\ServiceOverride
	 */
	public function setAlias($existsId, $newId) {
		if (isset($this->definition[$newId])) {
			throw new Exceptions\ServiceOverride('Service "' . $newId . '" already exists');
		}
		
		$this->definition[$newId] = $this->getDefinition($existsId);
	}
	
	/**
	 * Normalize definition
	 * 
	 * @param  mixed $definition Definition of service
	 * @return DefinitionInterface
	 */
	protected function normalizeDefinition($definition) {
		if ($definition instanceof DefinitionInterface) {
			return $definition;
		}
		
		if ($definition instanceof Closure) {
			return new Definition\CallbackDefinition($definition);
		}
		
		if (is_string($definition)) {
			return new Definition\ClassDefinition($definition);
		}
		
		return new Definition\InstanceDefinition($definition);
	}
	
	/**
	 * Get service by ID
	 * 
	 * @param  string $id   ID of service
	 * @param  mixed  $args Arguments for constructor of service
	 * @return mixed
	 * @throws Exceptions\ServiceNotFound
	 */
	public function get($id, $args = null) {
		if ($args !== null && func_num_args() > 2) {
			$args = func_get_args();
			array_shift($args);
		}
		
		if (isset($this->_definitions[$id])) {
			return $this->_definitions[$id]->getService($args);
		}
		
		foreach ($this->_locators as $locator) {
			if ($locator->has($id)) {
				return $locator->get($id, $args);
			}
		}
			
		throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
	}

	/**
	 * Is service available for obtain ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 */
	public function has($id) {
		if (isset($this->_definitions[$id])) {
			return true;
		}
		
		foreach ($this->_locators as $locator) {
			if ($locator->has($id)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get definition of service by service ID
	 * 
	 * @param  string $id ID of service
	 * @return DefinitionInterface
	 * @throws Exceptions\ServiceNotFound
	 */
	public function getDefinition($id) {
		if (isset($this->_definitions[$id])) {
			return $this->_definitions[$id];
		}
		
		throw new Exceptions\ServiceNotFound('Unable to found the service "' . $id . '"');
	}
	
	/**
	 * Has service initialized ?
	 * 
	 * @param  string $id   ID of service
	 * @param  mixed  $args Arguments which was service initialized
	 * @return bool
	 * @throws Exceptions\ServiceNotFound
	 */
	public function hasInitialized($id, $args = null) {
		return $this->getDefinition($id)->hasInitialized($args);
	}
	
	/**
	 * Remove service
	 * 
	 * @param string $id ID of service
	 */
	public function remove($id) {
		unset($this->_definitions[$id]);
	}
	
	/**
	 * Add fallback locator
	 * 
	 * @param  LocatorInterface $locator Locator instance
	 * @param  string           $id      Locator unique ID
	 * @return Container
	 */
	public function addLocator(LocatorInterface $locator, $id = null) {
		if ($id === null) {
			$this->_locators[] = $locator;
		} else {
			$this->_locators[$id] = $locator;
		}
		
		return $this;
	}
	
	/**
	 * Get locator by ID
	 * 
	 * @param  string | int $id
	 * @return LocatorInterface | null
	 */
	public function getLocator($id) {
		if (isset($this->_locators[$id])) {
			return $this->_locators[$id];
		}
	}
	
	/**
	 * Set service definition
	 * 
	 * @param string $id ID of service
	 * @param mixed  $definition
	 */
	public function __set($id, $definition) {
		$this->set($id, $definition);
	}
	
	/**
	 * Get service by ID
	 * 
	 * @param string $id ID of service
	 */
	public function __get($id) {
		return $this->get($id);
	}
	
	/**
	 * Is service available for obtain ?
	 * 
	 * @param  string $id ID of service
	 * @return bool
	 */
	public function __isset($id) {
		return $this->has($id);
	}
	
	/**
	 * Remove service
	 * 
	 * @param string $id ID of service
	 */
	public function __unset($id) {
		$this->remove($id);
	}
	
}