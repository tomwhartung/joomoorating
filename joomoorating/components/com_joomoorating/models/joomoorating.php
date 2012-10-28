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

jimport('joomla.application.component.model');

/**
 * Model for JoomooRating Component
 */
class JoomooRatingModelJoomooRating extends JoomoobaseModelJoomoobaseDb
{
	/**
	 * sql statement to update correct row with the rating
	 * @access protected
	 * @var string
	 */
	protected $_updateVote = '';

	/**
	 * Constructor - just a formality for this component
	 * @access public
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_tableName = "#__joomoorating";
	}
	/**
	 * Method to count a vote; called when task = 'vote'
	 * @access public
	 * @return mixed object if successful else false
	 */
	public function vote( $rating=null )
	{
		// print "Hello from JoomooratingController::vote() in file controller.php<br />\n";

		$id = JRequest::getVar( 'id', null );

		if ( $rating == null )
		{
			$rating = JRequest::getVar( 'rating', null );
		}

		if ( JOOMOO_RATING_MINIMUM <= $rating && $rating <= JOOMOO_RATING_MAXIMUM )
		{
			$db =& $this->getDBO();
			$this->_updateVote = 'UPDATE ' . $db->nameQuote($this->_tableName) .
				' SET ' . $db->nameQuote('vote_count') . ' = ' . $db->nameQuote('vote_count') . ' + 1, ' .
				 	$db->nameQuote('vote_total') . ' = ' . $db->nameQuote('vote_total') . ' + ' . $rating .
			 	' WHERE ' . $db->nameQuote('id') . ' = ' . $id;

			$db->setQuery( $this->_updateVote );
			$votedOk = $db->query( );
		}
		else
		{
			$message  = 'Rating (' . $rating . ') out of range!  ';
			$message .= 'Specify a value bewtween ' . JOOMOO_RATING_MINIMUM . ' and ' . JOOMOO_RATING_MAXIMUM . ' inclusive.';
			$this->setError( $message );
			$votedOk = FALSE;
		}

		return $votedOk;
	}
}
?>
