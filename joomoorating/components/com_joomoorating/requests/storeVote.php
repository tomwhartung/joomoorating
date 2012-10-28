<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoocomments
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php .
 */

/**
 * ================================================
 * This class runs outside of the joomla! framework
 * ================================================
 * Therefore we define _JEXEC (rather than check to see if it's defined)
 */
define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'JPATH_PLATFORM', dirname(__FILE__));
//print "<p>JPATH_BASE = '" . JPATH_BASE . "'</p>\n";
//print "<p>JPATH_PLATFORM = '" . JPATH_PLATFORM . "'</p>\n";

if ( !defined('DIRECTORY_SEPARATOR') )
{
	define( 'DIRECTORY_SEPARATOR', "/" );
}
define('DS', DIRECTORY_SEPARATOR);

require_once "JoomooRatingUpdateDb.php";

$updateRating = new JoomooRatingUpdateDb();
$updateRating->updateDatabase();

?>
