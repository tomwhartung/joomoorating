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

require_once( JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'controllers'.DS.'joomoobase.php' );
require_once( JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'models'.DS.'joomoobaseDb.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'joomoorating.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'joomoorating.php' );

JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' );  // enables JTable to find subclasses in tables subdir.

$controller = new JoomooRatingController( );          // Create the controller

$controller->execute( JRequest::getVar( 'task' ) );   // Perform the Request task
$controller->redirect();                              // Redirect if set by the controller
?>
