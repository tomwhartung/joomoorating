<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  JoomooRating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * controller for managing JoomooRating table in DB
 */
class JoomooRatingController extends JoomooBaseController
{
	/**
	 * Constructor: set model name in parent class
	 */
	public function __construct( $default = array() )
	{
		parent::__construct( $default );
		$this->_modelName = 'JoomooRating';
	}

	/**
	 * get model and view for component and call display() in view
	 * --> called by framework when task is not handled by another method in this class (i.e. when task is blank)
	 * @access public
	 * @return void
	 */
	public function display()
	{
		// print "Hello from JoomooRatingController::display()<br />\n";

		$model =& $this->getModel( $this->getModelName() );      // instantiates model class

		$view  =& $this->getView( 'JoomooRating', 'html' );      // 'html': use view.html.php (not view.php)
		$view->setModel( $model, true );                         // true: this is the default model

		$view->display();
	}
}
?>
