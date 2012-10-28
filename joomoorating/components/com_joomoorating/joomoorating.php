<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoorating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once (JPATH_COMPONENT.DS.'assets'.DS.'constants.php');   // Require constants
require_once (JPATH_COMPONENT.DS.'controller.php');   // Load the controller code

$controller = new JoomooRatingController( );          // Create the controller
$controller->execute(JRequest::getCmd('task'));       // Perform the Request task
$controller->redirect();                              // Redirect if set by the controller
?>
