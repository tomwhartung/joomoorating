<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoorating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Joomla class interface to #__joomoorating table
 * Defines column names and database methods for the joomoorating table
 */
class TableJoomooRating extends JTable
{
	/**
	 * @var int Primary Key
	 */
	public $id = null;
	/**
	 * @var int content id: foreign key to jos_content table
	 */
	public $contentid = null;
	/**
	 * @var int gallery image id: foreign key to jos_galleryimages table
	 */
	public $galleryimage = null;
	/**
	 * @var int number of votes submitted
	 */
	public $vote_count = null;
	/**
	 * @var int total of all votes submitted
	 */
	public $vote_total;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__joomoorating', 'id', $db );

		// print "Hello from TableJoomooRating::__construct()<br />\n";
	}

	/**
	 * Validator: ensure required values are set
	 * @return boolean True if values are valid else False
	 */
	public function check()
	{
		// print "Hello from TableJoomooRating::check()<br />\n";

		if ( ! (is_numeric($this->contentid) && 0 < $this->contentid) &&
		     ! (is_numeric($this->galleryimageid) && 0 < $this->galleryimageid)   )
		{
			$message  = 'Unable to store comment.  ';
			$message .= 'You must specify either a contentid ("' . $this->contentid . '") or a ';
			$message .= 'galleryimageid ("' . $this->galleryimageid . '").';
			$this->setError( JText::_($message) );
			return False;
		}

		if ( ! isset($this->vote_count) || ! is_numeric($this->vote_count) )
		{
			$this->setError( JText::_('Value for vote_count (' . $this->vote_count . ') must be specified and numeric.') );
			return false;
		}

		if ( ! isset($this->vote_total) || ! is_numeric($this->vote_total) )
		{
			$this->setError( JText::_('Value for vote_total (' . $this->vote_total . ') must be specified and numeric.') );
			return false;
		}

		return true;
	}
}
