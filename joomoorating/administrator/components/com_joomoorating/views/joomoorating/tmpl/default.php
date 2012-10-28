<?php
/**
 * @author      Tom Hartung <webmaster@tomhartung.com>
 * @package     Joomla
 * @subpackage  Joomoorating
 * @copyright   Copyright (C) 2010 Tom Hartung. All rights reserved.
 * @since       1.5
 * @license     GNU/GPL, see LICENSE.php
 */
/*
 * default.php: when task not handled by controller (e.g., it's blank), display rows from table
 * --------------------------------------------------------------------------------------------
 * call model code to get data from DB
 * call function defined in this file to produce the HTML
 */

defined( '_JEXEC' ) or die( 'Restricted access' );      // no direct access
// print "Hello from tmpl/default.php.<br />\n";

JToolBarHelper::title( JText::_( 'Joomoo Rating: View Database Records' ), 'generic.png' );
$document = & JFactory::getDocument();
$document->setTitle(JText::_('Joomoo Rating: View Database Records'));

//
// Get data from the model and list the rows
//
// $tableName  =& $this->get( 'tableName'  );    // calls getTableName () in the model
$rows       =& $this->get( 'Rows' );             // calls getRows() in the model
$pagination =& $this->get( 'Pagination' );       // calls getPagination() in the model
$lists      =& $this->get( 'lists' );            // calls getLists() in the model

listRows( $rows, $pagination, $lists );

/**
 * outputs HTML to display the list of rows
 * @return void
 */
function listRows( $rows, $pagination, $lists )
{
	$option = JRequest::getCmd('option');

	// print "listRows function: option: \"" . $option . "\"<br />\n";

	jimport( 'joomla.filter.output' );

	$rowCount = count( $rows );
	$rowClassSuffix = 0;
	$maxCharsInDesc = 200;

	print '<form action="index.php" method="post" name="adminForm" id="adminForm">' . "\n";

	print ' <table class="adminlist">' . "\n";
	print '  <tr>' . "\n";
	print '   <th width="10%" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'Id', 'id', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="10%" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'Contentid', 'contentid', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="10%" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'Gallery Image Id', 'galleryimageid', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="20%" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'Vote Count', 'vote_count', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="20%" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'Vote Total', 'vote_total', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '   <th width="15%" style="text-align: center">Rating</th>' . "\n";

	print '   <th width="15%" style="text-align: center">';
	echo  JHTML::_('grid.sort', 'Timestamp', 'timestamp', $lists['order_Dir'], $lists['order']);
	print '</th>' . "\n";

	print '  </tr>' . "\n";

	for ( $rowNum = 0; $rowNum < $rowCount; $rowNum++ )
	{
		$row =& $rows[$rowNum];
		$row->vote_count == 0 ? $rating = 0 : $rating = round( $row->vote_total / $row->vote_count, 2 );

		print '  <tr class="row' . $rowClassSuffix . '">' . "\n";
		print '   <td style="text-align: center">'  . $row->id . "</td>\n";
		print '   <td style="text-align: center">'  . $row->contentid . "</td>\n";
		print '   <td style="text-align: center">'  . $row->galleryimageid . "</td>\n";
		print '   <td style="text-align: center">'  . $row->vote_count . "</td>\n";
		print '   <td style="text-align: center">'  . $row->vote_total . "</td>\n";
		print '   <td style="text-align: center">'  . $rating . "</td>\n";
		print '   <td style="text-align: center">'  . $row->timestamp . "</td>\n";
		print '  </tr>' . "\n";

		$rowClassSuffix = 1 - $rowClassSuffix;      // alternates between values of 0 and 1 (to no avail!)
	}

	if ( is_a($pagination, 'JPagination') )
	{
		print '  <tfoot>' . "\n";
		print '   <td colspan="7">' . $pagination->getListFooter() . "\n";
		print '   </td>' . "\n";
		print '  </tfoot>' . "\n";
	}
	else
	{
		$pagination_class_name = get_class($pagination);
		print '  <tfoot>' . "\n";
		print '   <td colspan="7">' . "\n";
		print '     Oops, WTF, pagination is a member of the "' . $pagination_class_name . '" class?!?';
		print '   </td>' . "\n";
		print '  </tfoot>' . "\n";
	}

	print ' </table>' . "\n";

	print ' <input type="hidden" name="option" value="' . $option . '" />' . "\n";
	print ' <input type="hidden" name="task" value="" />' . "\n";

	if ( is_a($pagination, 'JPagination') )
	{
		print ' <input type="hidden" name="list_limit" value="' . $pagination->limit . '" />' . "\n";
	}

//	print ' <input type="hidden" name="filter_order" value="';
//	print    $lists['order'] . '" />' . "\n";
//	print ' <input type="hidden" name="filter_order_Dir" value="';
//	print    $lists['order_Dir'] . '" />' . "\n";

	print '</form>' . "\n";
	print '' . "\n";
}
?>
