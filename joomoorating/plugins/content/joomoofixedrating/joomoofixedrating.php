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
// print "Loading plgContentJoomoofixedrating.php: this class = " . $this_class . "<br />\n";

/**
 * Plugin for Joomoo Rating
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 		1.5
 */
class plgContentJoomoofixedrating extends JPlugin
{
	/**
	 * placeholder regular expression indicating we want to place a constant, fixed rating into this article
	 * value is JOOMOO_FIXED_RATING_REGEX ensconced in delimiters; JOOMOO_FIXED_RATING_REGEX defined in
	 *    components/com_joomoorating/assets/constants.php
	 * @access private
	 * @var string
	 * @note we use a <br ...> tag so if plugin is missing or disabled we just get some whitespace
	 */
	private $_placeholderRegEx = null;
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
	 * subtitle for display under the table containing the rating bars
	 * @var int
	 */
	private $_ratingSubtitle;

	/**
	 * Constructor
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	public function __construct ( &$subject, $params )
	{
		parent::__construct( $subject, $params );

		//
		// Note that we want these to look just like the rating bars, so we use a lot of those files
		//
		$document =& JFactory::getDocument();  // JDocumentHTML object
		$document->addStyleSheet( DS.'components'.DS.'com_joomoobase'.DS.'assets'.DS.'joomoobase.css' );
		$document->addStyleSheet( DS.'components'.DS.'com_joomoorating'.DS.'assets'.DS.'joomoorating.css' );

		$baseConstantsFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoobase'.DS.'assets'.DS.'constants.php';
		$constantsFilePath = JPATH_SITE.DS.'components'.DS.'com_joomoorating'.DS.'assets'.DS.'constants.php';
		require_once( $baseConstantsFilePath );
		require_once( $constantsFilePath );

		$this->_placeholderRegEx = '&' . JOOMOO_FIXED_RATING_REGEX . '&';
	}

	//
	// --------------------------------------------------------------------------------------------
	// Main driver method that prints the rating, adding it to the article when it's being prepared
	// --------------------------------------------------------------------------------------------
	// It is followed by the private methods it uses
	//
	/**
	 * Fixed ratings are available for selected articles only, so we require that the placeholder be present
	 * if placeholder is present, generate rating and replace the placeholder in the article text
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
		$this->_setIds( $article );
		$this->_ratingText = '';         // substituted for placeholder when processing a content article
		$returnText = '';                // returned to caller when processing a joomoo gallery image

		//
		// check for the place-holder
		// if we need it we generate the fixed rating and just replace the placeholder with it
		//
		$articleHasPlaceholder = preg_match( $this->_placeholderRegEx, $article->text, $matches );
		$articleHasPlaceholder ?
			$ratingOkForThisArticle = TRUE : $ratingOkForThisArticle = FALSE;

		//	$article->text = '<br />ratingOkForThisArticle = ' . $ratingOkForThisArticle . '<br />' . $article->text;
		//	$article->text = '<br />this->_contentid = ' . $this->_contentid . '<br />' . $article->text;
		//	$article->text = '<br />print_r(article) = ' . print_r($article,true) . '<br />' . $article->text;

		if ( $ratingOkForThisArticle )
		{
			$this->_setRatingVariables( $matches[1] );   // matches[1] might contain some overrides
			$this->_ratingText .= $this->_getRatingXhtml( $article );
			//
			// As of 1.7.3 we are no longer getting the contentid in the article ($article->id) unless the article is on its own page, 
			// so if the contentid doesn't point to a specific article, take the placeholder out
			//
			if ( $this->_contentid > 0 )
			{
				$article->text = preg_replace( $this->_placeholderRegEx, $this->_ratingText, $article->text );
			}
			else
			{
				$article->text = preg_replace( $this->_placeholderRegEx, '', $article->text );
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
	 * set variables used to display the rating
	 * @access private
	 * @return void
	 */
	private function _setRatingVariables( $overridesString )
	{
		$this->_setImageFileRoot( );
		$this->_ratingValue = $this->params->get( 'rating_value', 10 );
		$this->_ratingSubtitle = $this->params->get( 'rating_subtitle', 'Voting disabled for this article' );

		//	$this->_ratingText .= '<br />overridesString = ' . $overridesString . '<br />';

		if ( $overridesString != null && 0 < strlen($overridesString) )
		{
			$overridesArray = explode( JOOMOO_FIXED_OVERRIDES_DELIMITER, $overridesString );
			foreach ( $overridesArray as $anOverride )
			{
				$anOverrideArray = explode( '=', $anOverride );
				$anOverrideName = trim( $anOverrideArray[0] );
				$anOverrideValue = trim( $anOverrideArray[1], "'\"`" );
				switch( $anOverrideName )
				{
					case 'rating_value':
						if ( JOOMOO_RATING_MINIMUM <= $anOverrideValue && $anOverrideValue <= JOOMOO_RATING_MAXIMUM )
						{
							$this->_ratingValue = $anOverrideValue;
						}
						break;
					case 'rating_subtitle':
						$this->_ratingSubtitle = $anOverrideValue;
						break;
				}
			}
		}

		$rating_label = $this->params->get( 'rating_label', 'Rating' );
		$captionClassAttr = 'class="joomoorating_current_value" ';
		$this->_ratingCaption = $rating_label . ': ' .
			'<span ' . $captionClassAttr . '>' . $this->_ratingValue . '</span>/' . JOOMOO_RATING_MAXIMUM;
	}
	/**
	 * set the color of the bars used in the rating
	 * @access private
	 * @return void
	 */
	private function _setImageFileRoot( )
	{
		$rating_bar_color = $this->params->get( 'rating_bar_color', JOOMOO_RATING_WHITE );

		switch ( $rating_bar_color )
		{
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
			case JOOMOO_RATING_BLACK:
				$this->_barColor = 'black';
				break;
			default:
			case JOOMOO_RATING_WHITE:
				$this->_barColor = 'white';
				break;
		}

		$rating_bar_background = $this->params->get( 'rating_bar_background', JOOMOO_RATING_TRANSPARENT);

		switch ( $rating_bar_background )
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
	 * assembles xhtml for fixed rating
	 * @access private
	 * @return string xhtml containing rating
	 */
	private function _getRatingXhtml( $article )
	{
		$ratingValue = $this->_ratingValue;
		$ratingXhtml  = '';

		$ratingXhtml .= '<center>' . "\n";
		$ratingXhtml .= ' <table class="joomoorating_bars">' . "\n";
		$ratingXhtml .= '  <caption>' . $this->_ratingCaption . '</caption>' . "\n";
		$ratingXhtml .= '  <tr>' . "\n";

		for ( $ratingSub = JOOMOO_RATING_MINIMUM; $ratingSub <= JOOMOO_RATING_MAXIMUM; $ratingSub++ )
		{
			$lightFileName  = $this->_imageFileRoot . '-light-'  . $ratingSub . '.gif';
			$normalFileName = $this->_imageFileRoot . '-normal-' . $ratingSub . '.gif';
			$ratingValue == $ratingSub ? $rootedFileName = $normalFileName : $rootedFileName = $lightFileName;
			$imgTag = '     <img ' . 'class="joomoorating_bar" src="' . $rootedFileName . '" />' . "\n";
			$ratingXhtml .= '   <td class="joomoorating_bar">' . "\n" . $imgTag . '   </td>' . "\n";
		}

		$ratingXhtml .= '  </tr>' . "\n";

		if ( 0 < strlen($this->_ratingSubtitle) && 0 < $this->_galleryimageid )
		{
			$this->_ratingSubtitle = preg_replace( '&article&', 'gallery image', $this->_ratingSubtitle );
		}
		$ratingXhtml .= '  <tr><td colspan="' . (JOOMOO_RATING_MAXIMUM) . '" class="joomoorating_vote_count">' .
			$this->_ratingSubtitle . '</td></tr>' . "\n";

		$ratingXhtml .= ' </table>' . "\n";
		$ratingXhtml .= '</center>' . "\n";

		return $ratingXhtml;
	}
}
