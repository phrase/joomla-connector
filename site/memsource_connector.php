<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::registerPrefix('MemsourceConnector', JPATH_COMPONENT_ADMINISTRATOR, false, true);
JLoader::registerPrefix('MemsourceConnector', JPATH_COMPONENT, false, true);

define('MEMSOURCE_CONNECTOR_MODEL', 'MemsourceConnectorModels');

$controller = new MemsourceConnectorController;
$controller->execute(JFactory::getApplication()->input->get('task', 'display'));
$controller->redirect();
