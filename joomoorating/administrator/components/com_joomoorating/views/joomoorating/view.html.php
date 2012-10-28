<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoorating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * defines view class for joomoorating component back end
 */
class JoomooRatingViewJoomooRating extends JView
{
	function display($tpl=null)
	{
		JToolBarHelper::title( JText::_( 'Joomoo Rating' ) );
	//	JToolBarHelper::preferences( 'com_joomoorating', '150' );    // we have no parameters - yet.

		JHTML::_('behavior.tooltip');

		//
		// Help screens are set up at a server over which I have no control
		// See http://help.joomla.org/content/view/1955/214/ and
		//    backend -> Site -> System -> System Settings -> Help Site
		// For now I am just putting instructions in tmpl/default.php
		//

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('Joomoo Rating'));

		parent::display($tpl);
	}
}
