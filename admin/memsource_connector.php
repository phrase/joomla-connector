<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_memsource_connector'))
{
	die('error');
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

define('MEMSOURCE_CONNECTOR_MODEL', 'MemsourceConnectorModels');

JLoader::registerPrefix('MemsourceConnector', JPATH_COMPONENT_ADMINISTRATOR, false, true);

$controller = JControllerLegacy::getInstance('MemsourceConnector');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
