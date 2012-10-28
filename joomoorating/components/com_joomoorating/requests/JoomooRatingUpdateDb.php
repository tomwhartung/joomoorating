<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoorating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php .
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once "../assets/constants.php";
require_once "../../com_joomoobase/requests/JoomoobaseUpdateDb.php";
require_once "../../../administrator/components/com_joomoorating/tables/joomoorating.php";

/**
 * Ajax web service to update a ratingrow in the database
 * This class runs outside of the joomla! framework
 */
class JoomooRatingUpdateDb extends JoomoobaseUpdateDb
{
	/**
	 * sql statement to update correct row with the rating
	 * @access protected
	 * @var string
	 */
	protected $_updateVote = '';

	/**
	 * constructor
	 * @access public
	 */
	public function __construct()
	{
		//	print "Hello from JoomooRatingUpdateDb::__construct()<br />\n";

		parent::__construct();
		$this->_tableName = '#__joomoorating';
	}

	/**
	 * driver function to increment count columns in the database
	 * @access public
	 * @return void
	 */
	public function updateDatabase( )
	{
		//	print "Hello from JoomooRatingUpdateDb::updateDatabase()<br />\n";

		$data = JRequest::get( 'post' );
		$this->_id = $data['ratingRowId'];

		if ( $this->_id )
		{
			$ratingInstance = $data['ratingInstance'];
			$ratingValue = $data['ratingValue'];

			if ( JOOMOO_RATING_MINIMUM <= $ratingValue && $ratingValue <= JOOMOO_RATING_MAXIMUM )
			{
				$db =& $this->_getDb();
				$this->_updateVote = 'UPDATE ' . $db->nameQuote($this->_tableName) .
					' SET ' . $db->nameQuote('vote_count') . ' = ' . $db->nameQuote('vote_count') . ' + 1, ' .
				 		$db->nameQuote('vote_total') . ' = ' . $db->nameQuote('vote_total') . ' + ' . $ratingValue .
			 		' WHERE ' . $db->nameQuote('id') . ' = ' . $this->_id;

				$db->setQuery( $this->_updateVote );
				$updatedOk = $db->query();
				if ( $updatedOk )
				{
					$getValue = 'SELECT ' . $db->nameQuote('vote_count') . ',' . $db->nameQuote('vote_total') .
						'FROM' . $db->nameQuote($this->_tableName) .
						'WHERE ' . $db->nameQuote('id') . '=' . $this->_id . ';';
					$db->setQuery( $getValue );
					$result = $db->loadObject();
					is_numeric($result->vote_count) && 0 < $result->vote_count ?
						$newRatingValue = round( $result->vote_total / $result->vote_count, 1 ) :
						$newRatingValue = 'An internal error occurred: vote_count is invalid.';
					print JOOMOO_RATING_SAVED_OK . RATING_RESPONSE_DELIMITER .
						$ratingInstance . RATING_RESPONSE_DELIMITER .
						$newRatingValue . RATING_RESPONSE_DELIMITER .
						$result->vote_count;
				}
				else
				{
					//	print "updateDatabase(): query = \"$query\"<br />\n";
					$this->_message = $db->getError();
					print "Error running query (' . $this->_updateVote . '): " . $this->_message;
				}
			}
			else
			{
				print 'Oops, an error occurred while processing your vote - invalid value specified: "' . $ratingValue . '".';
			}
		}
		else
		{
			print 'Oops, an error occurred while processing your vote - no id specified.';
		}

		return $updatedOk;
	}
}
?>
