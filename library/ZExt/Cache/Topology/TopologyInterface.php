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

namespace ZExt\Cache\Topology;

use ZExt\Topology\Descriptor;

/**
 * Topology interface
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Topology
 * @author     Mike.Mirten
 * @version    1.0
 */
interface TopologyInterface {
	
	const TOPOLOGY_FRONTEND  = Descriptor::TYPE_PRIMARY;
	const TOPOLOGY_BACKEND   = Descriptor::TYPE_WARNING;
	const TOPOLOGY_DECORATOR = Descriptor::TYPE_DEFAULT;
	const TOPOLOGY_SPECIAL   = Descriptor::TYPE_ALERT;
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology();
	
	/**
	 * Get the unique ID of the Topology element
	 * 
	 * @return string Hexadecimal ID
	 */
	public function getTopologyId();
	
}