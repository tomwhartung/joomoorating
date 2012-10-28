<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoorating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * joomoorating Component Controller
 *
 */
class JoomooRatingController extends JController
{
	/**
	 * path to code for parent model class - in joomooobase
	 * @access private
	 * @var string file path
	 */
	private $_parentModelPath;
	/**
	 * path to code for rating model class
	 * @access private
	 * @var string file path
	 */
	private $_ratingModelPath;
	/**
	 * model supporting access to rating table in DB
	 * @access private
	 * @var instance of JoomooRatingModelJoomooRating
	 */
	private $_ratingModel = '';

	/**
	 * Constructor: set the model paths
	 * @access public
	 */
	public function __construct( $default = array() )
	{
		parent::__construct( $default );

		// print "Hello from JoomooRatingController::__construct()<br />\n";

		$this->_parentModelPath = 'components'.DS.'com_joomoobase'.DS.'models'.DS.'joomoobaseDb.php';
		$this->_ratingModelPath = 'components'.DS.'com_joomoorating'.DS.'models'.DS.'joomoorating.php';
	}

	/**
	 * Unused - we either post and redirect or do nothin' - kept as kind of a safety-net
	 * @access public
	 */
	public function display()
	{
		parent::display();
	}
	/**
	 * Method to count a vote; called when task = 'vote'
	 * @access public
	 */
	public function vote()
	{
		// print "Hello from JoomooratingController::vote() in file controller.php<br />\n";

		$link = JRequest::getVar( 'readmore_link', 'index.php' );

		require_once $this->_parentModelPath;
		require_once $this->_ratingModelPath;
		$this->_ratingModel = new JoomooRatingModelJoomooRating();

		$rating = JRequest::getVar( 'rating', null );
		$votedOk = $this->_ratingModel->vote( $rating );
		$message = '';

		if ( $votedOk )
		{
			$message .= 'Thanks for your vote (' . $rating . ')!';
		}
		else
		{
			$errorMessage = $this->_ratingModel->getError();
			if ( 0 < strlen($errorMessage) )
			{
				$message .= "Error saving vote: " . $errorMessage . '<br />';
				$message .= 'Please try again.';
			}
			else
			{
				$message .= 'Something went wrong and we are unable to save your vote at this time.<br />';
				$message .= 'Please try again later.';
			}
		}

		//	print "post_comment not redirecting; link = " . $link . "; message = " . $message . "<br />\n";
		$this->setRedirect( $link, $message );

		return $votedOk;
	}
}
?>
