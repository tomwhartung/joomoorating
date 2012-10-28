<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  joomoorating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     TBD.
 */
	//
	// constants.php: constants and default values
	// -------------------------------------------
	/**
	 * defining range of values for ratings - if you change these you must ensure images exist to support the new range!
	 */
	define ( 'JOOMOO_RATING_MINIMUM', 1 );
	define ( 'JOOMOO_RATING_MAXIMUM', 10 );
	/**
	 * text to print when user must be logged in to vote
	 */
	define ( 'JOOMOO_RATING_LOG_IN_TO_VOTE', 'Log in to vote!' );
	/**
	 * response from server when ajax request to rate an article or image is successful
	 */
	define ( 'JOOMOO_RATING_SAVED_OK', 'Vote saved - Thanks!' );
	/**
	 * delimiter used in response from server (note to self: delete if we decide to use xml or jason...)
	 */
	define ( 'RATING_RESPONSE_DELIMITER', '|' );
	/**
	 * possible values for where_in_article parameter
	 */
	define ( 'JOOMOO_RATING_ABOVE_ARTICLE', 'a' );
	define ( 'JOOMOO_RATING_BELOW_ARTICLE', 'b' );
	/**
	 * possible values for where_on_gallery_page parameter
	 */
	define ( 'JOOMOO_RATING_ABOVE_IMAGE', 'a' );
	define ( 'JOOMOO_RATING_BELOW_IMAGE', 'b' );
	define ( 'JOOMOO_RATING_BELOW_DESCRIPTION', 'd' );
	/**
	 * representing possible values for rating_bar_color and rating_bar_background parameters defined in joomoorating.xml
	 */
	define ( 'JOOMOO_RATING_BLACK',  'n' );  // 'n' for noir because blue uses b
	define ( 'JOOMOO_RATING_BLUE',   'b' );
	define ( 'JOOMOO_RATING_GREEN',  'g' );
	define ( 'JOOMOO_RATING_RED',    'r' );
	define ( 'JOOMOO_RATING_YELLOW', 'y' );
	define ( 'JOOMOO_RATING_WHITE',  'w' );
	define ( 'JOOMOO_RATING_TRANSPARENT', 't' );
?>
