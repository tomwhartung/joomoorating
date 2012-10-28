<?php
/**
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2010 - Tom Hartung.  All rights reserved.
 * @license		TBD.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

// // This is a ContentViewFrontpage object
// $this_class = get_class( $this );
// print "Loading plgContentJoomoorating.php: this class = " . $this_class . "<br />\n";

/**
 * Plugin for Joomoo Rating
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 		1.5
 */
class plgContentJoomoorating extends JPlugin
{
	/**
	 * placeholder regular expression indicating we want to allow rating of this article
	 * value is JOOMOO_RATING_PLACEHOLDER ensconced in delimiters; JOOMOO_RATING_PLACEHOLDER defined in
	 *    components/com_joomoobase/assets/constants.php
	 * @access private
	 * @var string
	 * @note we use a <br ...> tag so if plugin is missing or disabled we just get some whitespace
	 */
	private $_ratingPlaceholder;
	/**
	 * placeholder for joomoo fixed rating plugin
	 * value is JOOMOO_FIXED_RATING_REGEX ensconced in delimiters; also defined in com_joomoobase/assets/constants.php
	 * @access private
	 * @var string
	 * @note we use a <br ...> tag so if plugin is missing or disabled we just get some whitespace
	 */
	private $_fixedPlaceholder;
	/**
	 * User currently logged in - or not
	 * @var JUser Object
	 */
	private $_user;
	/**
	 * TRUE if current user can post rating else FALSE
	 * @var boolean
	 */
	private $_thisUserCanRate = FALSE;
	/**
	 * text we add to article or return to gallery image view
	 * @var string
	 */
	private $_ratingText;
	/**
	 * id of article in content table, or 0 if not processing content
	 * @var int
	 */
	private $_contentid = 0;
	/**
	 * id of image row in joomoo galleryimages table, or 0 if not processing a gallery image
	 * @var int
	 */
	private $_galleryimageid = 0;
	/**
	 * database object used to access the db
	 * @var string
	 */
	private $_database = null;
	/**
	 * name of database table
	 * @var string
	 */
	private $_tableName = '#__joomoorating';
	/**
	 * WHERE clause for query - based on whether contentid or galleryimageid is set
	 * @var string
	 */
	private $_whereClause = null;
	/**
	 * query string used to get the row containing the rating values
	 * @var string
	 */
	private $_query;
	/**
	 * row containing the rating values
	 * @var string
	 */
	private $_row;
	/**
	 * rating instance - to support using ajax when multiple ratings are on a page
	 * @var int
	 * @note this is a static variable
	 */
	private static $_ratingInstance = 0;
	/**
	 * we need to export some variables to javascript only once
	 * @var boolean true if values have already been exported, else false
	 * @note this is a static variable
	 */
	private static $_valuesExported = false;
	/**
	 * rating bar color
	 * @var int
	 */
	private $_barColor;
	/**
	 * rating bar background
	 * @var int
	 */
	private $_barBackground;
	/**
	 * file path: combination of rating bar color and rating bar background
	 * @var int
	 * @note eg. 'blue_on_white' or 'yellow_on_transparent'
	 */
	private $_barFilePath;
	/**
	 * constant part of file path and name of rating bar images
	 * @var string
	 */
	private $_imageFileRoot;
	/**
	 * the rating
	 * @var int
	 */
	private $_ratingValue;
	/**
	 * caption used for table containing the rating bars
	 * @var int
	 */
	private $_ratingCaption;

	/**
	 * Constructor
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	public function __construct ( &$subject, $params )
	{
		parent::__construct( $subject, $params );

		$document =& JFactory::getDocument();  // JDocumentHTML object
		$document->addStyleSheet( DS.'components'.DS.'com_joomoobase'.DS.'assets'.DS.'joomoobase.css' );
		$document->addStyleSheet( DS.'components'.DS.'com_joomoorating'.DS.'assets'.DS.'joomoorating.css' );
		$document->addScript( DS.'components'.DS.'com_joomoobase'.DS.'javascript'.DS.'myTypeOf.js' );
		$document->addScript( DS.'components'.DS.'com_joomoobase'.DS.'javascript'.DS.'JoomooRequest.js' );
		$document->addScript( DS.'components'.DS.'com_joomoorating'.DS.'javascript'.DS.'JoomooRatingLib.js' );
		$document->addScript( DS.'components'.DS.'com_joomoorating'.DS.'javascript'.DS.'JoomooRatingAjax.js' );

		$baseConstantsFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'assets'.DS.'constants.php';
		$constantsFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoorating'.DS.'assets'.DS.'constants.php';
		require_once( $baseConstantsFilePath );
		require_once( $constantsFilePath );

		$this->_ratingPlaceholder = '&' . JOOMOO_RATING_PLACEHOLDER . '&';
		$this->_fixedPlaceholder = '&' . JOOMOO_FIXED_RATING_REGEX . '&';

		$plugin =& JPluginHelper::getPlugin( 'content', 'joomoorating' );
		$this->_user = & JFactory::getUser();
		$allow_anonymous = $this->params->get( 'allow_anonymous', 0 );

		if ( 0 < $this->_user->id || $allow_anonymous )
		{
			$this->_thisUserCanRate = TRUE;
		}
	}

	//
	// --------------------------------------------------------------------------------------------
	// Main driver method that prints the rating, adding it to the article when it's being prepared
	// --------------------------------------------------------------------------------------------
	// It is followed by the private methods it uses
	//
	/**
	 * If we allow rating of all articles, generate rating xhtml and add it to the article
	 * else if placeholder is present, generate rating and replace the placeholder in the article text
	 * else - don't change the article
	 *
	 * @param	string		The type of content being passed, eg. 'com_content.article'
	 * @param 	object		The article object.  Note $article->text is also available
	 * @param 	object		The article params
	 * @param 	int			The 'page' number
	 * @access	public
	 * @return	string
	 */
	public function onContentPrepare( $context, &$article, &$params=null, $page=0 )
	{
		$this->_ratingText = '';
		$this->_whereClause = null;
		$this->_setIds( $article );
		$this->_getRatingRow( );

		$this->_ratingInstance++;
		$this->_setRatingVariables( );

		//
		// if we are processing a content article and all_articles param is set (in back end)
		//    if article doesn't already have the placeholder,
		//        we add it to the beginning or end of the article as appropriate
		// else we check for the place-holder
		// then if we need it we generate the rating and just replace the placeholder with it
		//
		$ratingOkForThisArticle = FALSE;
		$hasRatingPlaceholder = preg_match( $this->_ratingPlaceholder, $article->text );
		$hasFixedPlaceholder = preg_match( $this->_fixedPlaceholder, $article->text );
		$all_articles = $this->params->get( 'all_articles', 1 );
		if ( $this->_galleryimageid > 0 )
		{
			$ratingOkForThisArticle = TRUE;
		}
		else if ( $this->_contentid > 0 && $all_articles && ! $hasFixedPlaceholder )
		{
			$ratingOkForThisArticle = TRUE;
			if ( ! $hasRatingPlaceholder )
			{
				$where_in_article = $this->params->get( 'where_in_article', JOOMOO_RATING_BELOW_IMAGE );
				$where_in_article == JOOMOO_RATING_ABOVE_ARTICLE ? 
					$article->text = JOOMOO_RATING_PLACEHOLDER . $article->text :
					$article->text .= JOOMOO_RATING_PLACEHOLDER;
				$hasRatingPlaceholder = TRUE;
			}
		}
		else if ( $hasRatingPlaceholder )
		{
			$ratingOkForThisArticle = TRUE;
		}

		//	$this->_ratingText .= '<br />this->_row->id = ' . $this->_row->id . '<br />';
		//	$this->_ratingText .= 'this->_galleryimageid = ' . $this->_galleryimageid . '<br />';
		//	$article->text = '<br />article!!!<br />article  = ' . print_r($article,true)  . '<br />end of article!!!<br />' . $article->text;
		//	$article->text = '<br />this->_row!!!<br />this->row  = ' . print_r($this->_row,true)  . '<br />end of this->_row!!!<br />' . $article->text;
		//	$article->text = '<br />this->_row->id  = ' . $this->_row->id  . '<br />' . $article->text;
		//	$article->text = '<br />this->_contentid = ' . $this->_contentid . '<br />' . $article->text;
		//	$article->text = '<br />this->_galleryimageid = ' . $this->_galleryimageid . '<br />' . $article->text;
		//	$article->text = '<br />this->_thisUserCanRate = ' . $this->_thisUserCanRate . '<br />' . $article->text;
		//	$article->text = '<br />ratingOkForThisArticle = ' . $ratingOkForThisArticle . '<br />' . $article->text;

		$returnText = '';
		if ( $ratingOkForThisArticle )
		{
			if ( ! self::$_valuesExported )
			{
				$this->_ratingText .= $this->_exportValuesToJavascript();
				$this->_valuesExported = true;
			}
			if ( isset($article->readmore_link) )
			{
				$readmore_link = htmlspecialchars( $article->readmore_link );
			}
			else
			{
				$readmore_link = '';
			}
			//
			// As of 1.7.3 we are no longer getting the contentid in the article ($article->id) unless the article is on its own page,
			// so if the contentid doesn't point to a specific article, take the placeholder out
			//
			$this->_ratingText .= $this->_getRatingXhtml( $readmore_link );
			if ( $this->_contentid > 0 )
			{
				$article->text = preg_replace( $this->_ratingPlaceholder, $this->_ratingText, $article->text );
			}
			else
			{
				$article->text = preg_replace( $this->_ratingPlaceholder, '', $article->text );
				$returnText = $this->_ratingText;        // when processing a gallery image, return the rating xhtml
			}
		}

		return $returnText;
	}
	/**
	 * set the *id member variables: contentid and galleryimageid
	 * @access private
	 * @return void
	 */
	private function _setIds( $article )
	{
		if ( isset($article->galleryimageid) && 0 < $article->galleryimageid )
		{
			$this->_contentid = 0;
			$this->_galleryimageid = $article->galleryimageid;
		}
		else if ( isset($article->id) && 0 < $article->id )
		{
			$this->_contentid = $article->id;
			$this->_galleryimageid = 0;
		}
		else
		{
			$this->_contentid = 0;
			$this->_galleryimageid = 0;
		}
	}
	/**
	 * set variables used to display the rating - must be run only after we've gotten the data
	 * @access private
	 * @return void
	 */
	private function _setRatingVariables( )
	{
		$this->_setImageFileRoot( );

		$rating_label = $this->params->get( 'rating_label', 'Rating' );
		$captionIdAttr = 'id="joomoorating_rating_value_' . $this->_ratingInstance . '" ';
		$captionClassAttr = 'class="joomoorating_current_value" ';

		if ( $this->_row->vote_count )
		{
			$ratingValueOneDecimal = round( $this->_row->vote_total / $this->_row->vote_count, 1 );
			$this->_ratingValue = round( $ratingValueOneDecimal );
			$this->_ratingCaption = $rating_label . ': ' .
				'<span ' . $captionIdAttr . $captionClassAttr . '>' . $ratingValueOneDecimal . '</span>/' . JOOMOO_RATING_MAXIMUM;
		}
		else
		{
			$this->_ratingValue = 0;
			$this->_ratingCaption = '<span ' . $captionIdAttr . $captionClassAttr . '>' . 'Be the first to vote!' . '</span>';
		}
	}
	/**
	 * set the color of the bars used in the rating
	 * @access private
	 * @return void
	 */
	private function _setImageFileRoot( )
	{
		$rating_bar_color = $this->params->get( 'rating_bar_color', JOOMOO_RATING_WHITE );

		switch ( $this->params->get('rating_bar_color') )
		{
			case JOOMOO_RATING_BLACK:
				$this->_barColor = 'black';
				break;
			case JOOMOO_RATING_BLUE:
				$this->_barColor = 'blue';
				break;
			case JOOMOO_RATING_GREEN:
				$this->_barColor = 'green';
				break;
			case JOOMOO_RATING_RED:
				$this->_barColor = 'red';
				break;
			case JOOMOO_RATING_YELLOW:
				$this->_barColor = 'yellow';
				break;
			default:
			case JOOMOO_RATING_WHITE:
				$this->_barColor = 'white';
				break;
		}

		$rating_bar_background = $this->params->get( 'rating_bar_background', JOOMOO_RATING_TRANSPARENT );

		switch ( $this->params->get('rating_bar_background') )
		{
			case JOOMOO_RATING_BLACK:
				$this->_barBackground = 'black';
				break;
			case JOOMOO_RATING_WHITE:
				$this->_barBackground = 'white';
				break;
			default:
			case JOOMOO_RATING_TRANSPARENT:
				$this->_barBackground = 'transparent';
				break;
		}

		$this->_barFilePath = $this->_barColor . '_on_' . $this->_barBackground;

		$this->_imageFileRoot = DS. 'components' .DS. 'com_joomoorating' .DS.
			'images' .DS. 'bars' .DS. $this->_barFilePath .DS. $this->_barFilePath;
	}
	/**
	 * export constants and other values needed to validate the form to javascript
	 * @access private
	 * @return string html containing values
	 */
	private function _exportValuesToJavascript( )
	{
		$hover_width_bump        = $this->params->get( 'hover_width_bump', 2 );
		$rating_label            = $this->params->get( 'rating_label', 'Rating' );
		$max_consecutive_ratings = $this->params->get( 'max_consecutive_ratings', 1 );
		is_numeric($max_consecutive_ratings) ?
			$maxConsecutiveString = 'parseInt(' . $max_consecutive_ratings . ')' :
			$maxConsecutiveString = 'Infinity' . "\n";

		$values = '';
		$values .= '<script type="text/javascript">' . "\n";
		$values .= ' //<![CDATA[ ' . "\n";
		$values .= '  JoomooRatingLib.hover_width_bump = "' . $hover_width_bump . '";' . "\n";
		$values .= '  JoomooRatingAjax.rating_label = "' . $rating_label . '";' . "\n";
		$values .= '  JoomooRatingAjax.max_consecutive_ratings = ' . $maxConsecutiveString . ';' . "\n";
		$values .= '  JoomooRatingAjax.JOOMOO_RATING_SAVED_OK = "' . JOOMOO_RATING_SAVED_OK . '";' . "\n";
		$values .= '  JoomooRatingAjax.RATING_RESPONSE_DELIMITER = "' . RATING_RESPONSE_DELIMITER . '";' . "\n";
		$values .= '  JoomooRatingLib.JOOMOO_RATING_MINIMUM = "' . JOOMOO_RATING_MINIMUM . '";' . "\n";
		$values .= '  JoomooRatingLib.JOOMOO_RATING_MAXIMUM = "' . JOOMOO_RATING_MAXIMUM . '";' . "\n";
		$values .= '  JoomooRatingLib.JOOMOO_RATING_LOG_IN_TO_VOTE = "' . JOOMOO_RATING_LOG_IN_TO_VOTE . '";' . "\n";
		$values .= '  JoomooRatingLib.imageFileRoot = "' . $this->_imageFileRoot . '";' . "\n";
		$values .= '  JoomooRatingLib.thisUserCanRate = "' . $this->_thisUserCanRate . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription = new Array();' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[1] = "'  . $this->params->get('rating_description_1','Very Low') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[2] = "'  . $this->params->get('rating_description_2','Fairly Low') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[3] = "'  . $this->params->get('rating_description_3','Low') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[4] = "'  . $this->params->get('rating_description_4','Somewhat Low') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[5] = "'  . $this->params->get('rating_description_5','Medium Low') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[6] = "'  . $this->params->get('rating_description_6','Medium High') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[7] = "'  . $this->params->get('rating_description_7','Somewhat High') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[8] = "'  . $this->params->get('rating_description_8','High') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[9] = "'  . $this->params->get('rating_description_9','Fairly High') . '";' . "\n";
		$values .= '  JoomooRatingLib.ratingDescription[10] = "' . $this->params->get('rating_description_10','Very High') . '";' . "\n";
		$values .= ' //]]>' . "\n";
		$values .= '</script>' . "\n";

		return $values;
	}
	/**
	 * assembles xhtml and javascript allowing user to rate article or gallery image
	 * @access private
	 * @return string xhtml containing rating form
	 */
	private function _getRatingXhtml( $readmore_link )
	{
		$ajax_or_full = $this->params->get( 'ajax_or_full', JOOMOO_USE_AJAX_OR_FULL );
		$ratingInstance = $this->_ratingInstance;
		$ratingValue = $this->_ratingValue;
		$this->_row->vote_count == 1 ? $voteOrVotes = ' vote' : $voteOrVotes = ' votes';
		$voteCountXhtml = 'Total:&nbsp;' . '<span class="joomoorating_current_value" id="joomoorating_vote_count_' . $ratingInstance . '">' .
			$this->_row->vote_count . $voteOrVotes . '</span>';
		$ratingXhtml  = '';

		if ( ! $readmore_link )
		{
			$readmore_link = 'index.php';
		}

		$hrefBase = 'index.php?option=com_joomoorating&id=' . $this->_row->id . '&task=vote&readmore_link=' . $readmore_link . '&rating=';

		$ratingXhtml .= '<center>' . "\n";
		$ratingXhtml .= ' <table class="joomoorating_bars">' . "\n";
		$ratingXhtml .= '  <caption>' . $this->_ratingCaption . '</caption>' . "\n";
		$ratingXhtml .= '  <tr>' . "\n";

		for ( $ratingSub = JOOMOO_RATING_MINIMUM; $ratingSub <= JOOMOO_RATING_MAXIMUM; $ratingSub++ )
		{
			$lightFileName  = $this->_imageFileRoot . '-light-'  . $ratingSub . '.gif';
			$normalFileName = $this->_imageFileRoot . '-normal-' . $ratingSub . '.gif';
			$ratingValue == $ratingSub ? $rootedFileName = $normalFileName : $rootedFileName = $lightFileName;
			$imgTagId = 'joomoorating_bar_' . $ratingInstance . '_' . $ratingSub;
			//
			// set up the instance- and ratingSub-specific event handlers
			//
			$mouseoverHandler = 'JoomooRatingLib.mouseoverRatingBar' . "('" . $ratingInstance . "','" . $ratingSub . "');";
			$mouseoutHandler  = 'JoomooRatingLib.mouseoutRatingBar'  . "('" . $ratingInstance . "','" . $ratingSub . "');";
			$onmouseoverAttr = 'onmouseover="return ' . $mouseoverHandler . '" ';
			$onmouseoutAttr  = 'onmouseout="return ' . $mouseoutHandler . '" ';
			$events = $onmouseoverAttr . ' ' . $onmouseoutAttr;
			if ( $this->_thisUserCanRate )
			{
				switch ( $ajax_or_full )
				{
					case JOOMOO_USE_AJAX_ONLY:
						$openATag = '';
						$closeATag = '';
						break;
					case JOOMOO_USE_FULL_ONLY:
						$openATag  = '   <a class="joomoorating_bar" href="' . $hrefBase . $ratingSub . '">' . "\n";
						$closeATag = '    <div style="text-align: center;">' . $ratingSub . '</div></a>' . "\n";
						break;
					default:
					case JOOMOO_USE_AJAX_OR_FULL:
						$openATag  = '    <noscript>' .
 						'   <a class="joomoorating_bar" href="' . $hrefBase . $ratingSub . '"></noscript>' . "\n";
						$closeATag = '    <noscript>' .
							'   <div style="text-align: center;">' . $ratingSub . '</div></a></noscript>' . "\n";
						break;
				}
			}
			else
			{
				$openATag = '';
				$closeATag = '';
			}
			if ( $ajax_or_full != JOOMOO_USE_FULL_ONLY )
			{
				$clickHandler = 'JoomooRatingLib.clickRatingBar(' . $this->_row->id . ',' . $ratingInstance . ',' . $ratingSub . ');';
				$events .= 'onclick="' . $clickHandler . '" ';
			}
			$imgTagIdAttr = 'id="' . $imgTagId . '" ';
			$imgTag = '     <img ' . $imgTagIdAttr . 'class="joomoorating_bar" src="' . $rootedFileName . '" ' . $events . ' />' . "\n";
			$ratingXhtml .= '   <td class="joomoorating_bar">' . "\n" . $openATag . $imgTag . $closeATag . '   </td>' . "\n";
		}

		$ratingXhtml .= '  </tr>' . "\n";
		$ratingXhtml .= '  <tr><td colspan="' . JOOMOO_RATING_MAXIMUM . '" class="joomoorating_vote_count">' .
			$voteCountXhtml . '</td></tr>' . "\n";

		$yourvoteIdAttr = 'id="joomoorating_yourvote_' . $ratingInstance . '" ';
		$yourvoteTextIdAttr = 'id="joomoorating_yourvote_text_' . $ratingInstance . '" ';
		$yourvoteValueIdAttr = 'id="joomoorating_yourvote_value_' . $ratingInstance . '" ';
		$yourvoteThanksIdAttr = 'id="joomoorating_yourvote_thanks_' . $ratingInstance . '" ';
		if ( $this->_thisUserCanRate )
		{
			$ajax_or_full == JOOMOO_USE_AJAX_ONLY ? 
				$yourvoteText = '<noscript><span class="joomoorating_js_off">' .
					'Enable javascript to vote.</span></noscript>' :
				$yourvoteText = 'Your vote: ';
		}
		else
		{
			$yourvoteText = JOOMOO_RATING_LOG_IN_TO_VOTE;
		}

		$ratingXhtml .= '  <tr>' . "\n";
		$ratingXhtml .= '   <td colspan="' . JOOMOO_RATING_MAXIMUM . '" class="joomoorating_yourvote" ' . $yourvoteIdAttr . '>' . "\n";
		$ratingXhtml .= '    <span ' . $yourvoteTextIdAttr . '>' . $yourvoteText . '</span>' .
			'<span class="joomoorating_current_value" ' . $yourvoteValueIdAttr . '></span>&nbsp;' .
			'<span ' . $yourvoteThanksIdAttr . '></span>' . "\n";
		$ratingXhtml .= '   </td>' . "\n";
		$ratingXhtml .= '  </tr>' . "\n";

		$ratingXhtml .= '  <tr><td colspan="' . JOOMOO_RATING_MAXIMUM . '">' . "\n";
		$ratingXhtml .= '   <script type="text/javascript">' . "\n";
		$ratingXhtml .= '    //<![CDATA[ ' . "\n";
		$ratingValue == 0 ? $needsFullCaption = 'true' : $needsFullCaption = 'false';
		$ratingXhtml .= '     JoomooRatingAjax.needsFullCaption[' . $ratingInstance . '] = ' . $needsFullCaption . ';' . "\n";
		$ratingXhtml .= '     JoomooRatingAjax.writeAjaxLog("' . $ratingInstance . '");' . "\n";     // Used for results of ajax request
		$ratingXhtml .= '     JoomooRatingLib.ratingValue[' . $ratingInstance . '] = ' . $ratingValue . ';' . "\n";
		$ratingXhtml .= '    //]]>' . "\n";
		$ratingXhtml .= '   </script>' . "\n";
		$ratingXhtml .= '  </td></tr>' . "\n";

		$ratingXhtml .= ' </table>' . "\n";
		$ratingXhtml .= '</center>' . "\n";

		return $ratingXhtml;
	}

	//
	// -------------------------------------------------------------------
	// private utility functions to support getting data from the database
	// -------------------------------------------------------------------
	//
	/**
	 * get the row containing the rating for this article or gallery image
	 * @access private
	 * @return object containing rating data
	 */
	private function _getRatingRow( )
	{
		if ( $this->_database == null )
		{
			$this->_database =& JFactory::getDBO();
		}

		$this->_query = 'SELECT id, vote_count, vote_total FROM ' . $this->_database->nameQuote( $this->_tableName ) .
			$this->_getWhereClause( );
		$this->_database->setQuery( $this->_query );
		$this->_row = $this->_database->loadObject();

		if ( $this->_row == null )
		{
		//	$this->_ratingText .= '<br />We are calling _insertRatingRow<br />';
			$this->_insertRatingRow();
		}

		return $this->_row;
	}
	/**
	 * get WHERE clause for query based on which id is set
	 * @access private
	 * @return string containing 'WHERE ...'
	 */
	private function _getWhereClause( )
	{
		if ( $this->_whereClause == null )
		{
			if ( $this->_contentid != 0 )
			{
				$this->_whereClause = ' WHERE ' . $this->_database->nameQuote('contentid') . ' = ' . $this->_contentid;
			}
			else if ( $this->_galleryimageid != 0 )
			{
				$this->_whereClause = ' WHERE ' . $this->_database->nameQuote('galleryimageid') . ' = ' . $this->_galleryimageid;
			}
			else
			{
				$this->_whereClause = ' WHERE ' . $this->_database->nameQuote('contentid') . ' = 0 ' .
					'AND ' . $this->_database->nameQuote('galleryimageid') . ' = 0';                       // should not occur!
			}
		}

		return $this->_whereClause;
	}
	/**
	 * insert a rating row for this article or gallery image
	 * @access private
	 * @return object containing the new rating data
	 */
	private function _insertRatingRow()
	{
		$this->_row = new stdClass();
		$this->_row->id = 0;
		$this->_row->contentid = $this->_contentid;
		$this->_row->galleryimageid = $this->_galleryimageid;
		$this->_row->vote_count = 0;
		$this->_row->vote_total = 0;

		$this->_database->insertObject( $this->_tableName, $this->_row );
		$this->_row->id = $this->_database->insertId();

		return $this->_row;
	}
}
