<?php 
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 **/

/**
 * Retrieve the values for a given field in the current item.
 * 
 * @param string
 * @return array
 **/
function item($field, $options=array())
{
    $helper = __get_item_helper();
    return $helper->item($field, $options);
}

/**
 * Retrieve the metadata for a specific element, given its name.
 * 
 * @todo Switch out dummy data with live database.
 * @param string Name of the element
 * @param string Possible fields include 'description', 'type name', 
 * 'type description', 'type regex', 'set name', 'set description'
 * @return string
 **/
function element_metadata($elementName, $field)
{
    switch ($field) {
        case 'description':
            return "Dummy description!";
            break;
        default:
            # code...
            break;
    }
}

/**
 * @access private
 * @return void
 **/
function __get_item_helper()
{
    require_once HELPER_DIR . DIRECTORY_SEPARATOR . 'Item.php';
    $helper = new Omeka_View_Helper_Item;
    return $helper;   
}

/**
 * Retrieve the set of values for item type elements.
 * 
 * @return array
 **/
function item_type_elements()
{
    $item = get_current_item();
    $fieldNames = get_db()->getTable('Element')->findNamesByItemType($item->item_type_id);
    $elementText = array();
    foreach ($fieldNames as $field) {
        $elementText[$field] = item($field);
    }
    return $elementText;
}

/**
 * Retrieve the form for the item type elements, based on the current item.
 *
 * The form input names correspond to Elements[element_id][order].  This allows
 * maximum flexibility of being able to associate data for any Element via the 
 * items form.
 * 
 * @todo Test with live data.
 *
 * @return string HTML
 **/
function item_type_elements_form()
{
    $item = get_current_item();
    $html = '';
    
    //Loop through all of the element records for the item's item type
    $elements = $item->getTypeElements();   
    foreach ($elements as $key => $element) {
        $html .= display_form_input_for_element($element);		
    }
    
    return $html;
}

/**
 * Retrieve the proper HTML for a form input for a given Element record.
 * 
 * Assume that the given element has access to all of its values (for example,
 * all values of a Title element for a given Item).
 *
 * This will output as many form inputs as there are values for a given
 * element.  In addition to that, it will give each set of inputs a label and
 * a span with class="tooltip" containing the description for the element.
 * This span can either be displayed, hidden with CSS or converted into a 
 * tooltip with javascript.
 *
 * All sets of form inputs for elements will be wrapped in a div with
 * class="field".
 *
 * @todo Modify HTML markup to work with more than one input per element.  
 * The CSS ID should not be applied to the form input because there can 
 * be more than one form input per element.  Perhaps it should be on the
 * div wrapper around the element.
 * @todo Plugins should be able to hook in to displaying elements in a certain
 * way.
 * @param Element
 * @return string HTML
 **/
function display_form_input_for_element($element)
{
    $html = '';
    
    $fieldId = text_to_id($element['name']);
    $fieldLabel = htmlentities($element['name']);
    $fieldDescription = htmlentities($element['description']);
    
    $input = '';
    
    //There can be an arbitrary # of values in element->text
    //It's an array (not hash) at this point
    $numFieldValues = count($element['text']);
    $numFieldValues = $numFieldValues ? $numFieldValues : 1;
    for ($i=0; $i < $numFieldValues; $i++) { 
        
        //The name of the input on the form
        $fieldName = "Elements[" . $element['id'] . "][$i]";
        
        //The value in the form field should be the text for that element
        $fieldValue = htmlentities($element['text'][$i]);
                    
        //Check the POST to see if there is a value for that field saved already
        $postValue = $_POST['Elements'][$element['id']][$i];
        $fieldValue = !empty($postValue) ? htmlentities($postValue) : $fieldValue;
        
        //Here is where plugins should hook in to deal with display of certain
        //elements
        
        //Create a form input based on the element type name
        switch ($element['type_name']) {
            //Tinytext => input type="text"
            case 'tinytext':
                $input .= '<input type="text" class="textinput" name="';
                $input .= $fieldName . '" id="' . $fieldId . '" value="';
                $input .= $fieldValue . '" />';
                break;
            //Text => textarea
            case 'text':
                $input .= '<textarea rows="15" cols="50" class="textinput"';
                $input .= ' name="' . $fieldName . '" id="' . $fieldId . '">';
                $input .= $fieldValue . "</textarea>\n\t";
                break;
            default:
                throw new Exception('Cannot display a form input for "' . 
                $element['name'] . '" if element type name is not given!');
                break;
        }
    }

    //Wrap the input with a <div class="field">
    $html .= '<div class="field">';
	$html .= '<label for="' . $fieldId . '">' . $fieldLabel;
	$html .= '</label>'."\n\t";
	
	$html .= $input;
	
	//Tooltips should be in a <span class="tooltip">
	$html .= '<span class="tooltip" id="' . $fieldId . '-tooltip">';
	$html .= $fieldDescription .'</span></div>';    
	
	return $html;
}

/**
 * Retrieve a valid citation for the current item.  
 * 
 * @internal Was previously located at Item::getCitation().  This made not a 
 * whole lot of sense though, given that it is very much an element of the View
 * and not directly related to the business logic of the app.
 * @todo Make sure this citation follows some sort of standard.  MLA? Other?
 * @todo Ideally this would be able to hook into plugins being able to define
 * new citation formats.  ZoterOmeka anyone?
 * @return string
 **/
function item_citation()
{
    if($citation = item('Citation')) {
		return $citation;
	}

	$cite = '';
    $cite .= item('Creator', 0);
    if ($cite != '') $cite .= ', ';
    $cite .= '"' . item('Title', ', ') . '". ';
    $cite .= '<em>'.settings('site_title').'</em>, ';
    $cite .= 'Item #'.item('id').' ';
    $cite .= '(accessed '.date('F d Y, g:i a').') ';
    return $cite;
}

/**
 * @access private
 * @param Omeka_Record
 * @return string
 **/
function url_for_record(Omeka_Record $record, $action, $controller = null)
{
    $options = array();
    // Inflect the name of the controller from the record class if no
    // controller name is given
    $options['controller'] = !$controller ? 
        strtolower(Inflector::pluralize(get_class($record))) : $controller;
    $options['id'] = $record->id;
    $options['action'] = $action;
    
    // Use the 'id' route for all urls pointing to records
    return url_for($options, 'id');
}

/**
 * Retrieve a URL for the current item
 * 
 * @param string Action to link to for this item.
 * @return string URL
 **/
function url_for_item($action = 'show')
{
    return url_for_record(get_current_item(), $action);
}

/**
 * Determine whether or not the current item belongs to a collection.
 * 
 * @param string|null The name of the collection that the item would belong
 * to.  If null, then this will check to see whether the item belongs to
 * any collection.
 * @param Item|null Check for this specific item record (current item if null).
 * @return boolean
 **/
function item_belongs_to_collection($name=null, $item=null)
{
    //Dependency injection
    if(!$item) {
        $item = get_current_item();
    }
    
    return (!empty($item->collection_id) and (!$name or $item->Collection->name == $name));
}

/**
 * @uses display_files()
 * @uses get_current_item()
 * @param array
 * @return string HTML
 **/
function display_files_for_item($options=array())
{
    $item = get_current_item();
    return display_files($item->Files, $options);
}

/**
 * @uses current_user_tags()
 * @uses get_current_item()
 * @param string
 * @return array
 **/
function current_user_tags_for_item()
{
    $item = get_current_item();
    return current_user_tags($item);
}

/**
 * Determine whether or not the item has any files associated with it.
 * 
 * @see has_files()
 * @uses Item::hasFiles()
 * @return boolean
 **/
function item_has_files()
{
    $item = get_current_item();
    return $item->hasFiles();
}

/**
 * Use this to choose an item type from a <select>
 * 
 * @uses ItemTypeTable::findAllForSelectForm()
 * @param array
 * @param string Selected value
 * @return string HTML
 **/
function select_item_type($props=array(), $value=null)
{
    $itemTypes = get_db()->getTable('ItemType')->findAllForSelectForm();
        
    return select(	$props,
			$itemTypes,
			$value);	
}

function select_item_type_for_item($props=array())
{
    $item = get_current_item();
    return select_item_type($props, $item->item_type_id);
}

/**
 * @see CollectionTable::findAllForSelectForm()
 * @param array
 * @param string
 * @return string
 **/
function select_collection($props = array(), $value=null)
{
    //$collectionInfo should be an array where key => collection_id, value =>
    //collection name.
    $collectionInfo = get_db()->getTable('Collection')->findAllForSelectForm();

    return select($props, $collectionInfo, $value);
}

/**
 * @see select_collection()
 */
function select_user($props = array(), $value=null)
{
    $userInfo = get_db()->getTable('User')->findAllForSelectForm();
    
    return select($props, $userInfo, $value);
}

/**
 * @see select_institution()
 */
function select_institution($props = array(), $value = null)
{
    $institutionInfo = get_db()->getTable('Entity')->findInstitutionsForSelectForm();
    
    return select($props, $institutionInfo, $value);
}

/**
 * Retrieve the current Item record
 * 
 * @access private
 * @param string
 * @return void
 **/
function get_current_item()
{
    return __v()->item;
}

/**
 * @access private
 * @see loop_items()
 * @param Item
 * @return void
 **/
function set_current_item(Item $item)
{
    $view = __v();
    $view->previous_item = $view->item;
    $view->item = $item;
}

/**
 * @access private
 */
function set_items_for_loop($items)
{
    $view = __v();
    $view->items = $items;
}

/**
 * @return boolean
 */
function has_items_for_loop()
{
    $view = __v();
    return ($view->items and count($view->items));
}

/**
 * Use in while statement to loop through a set of Item records.  This will
 * set the current item.
 * 
 * If the reset parameter is passed, it will reset the loop.
 *
 * @param boolean
 * @return boolean
 **/
function loop_items($reset=false)
{
    static $set = null;
    
    if(!$set) {
        $set = __v()->items;
    }
    
    //If we haven't reached the end of the loop, set the current item
    //in the loop and 
    if(list($key, $item) = each($set)) {
        set_current_item($item);
        return true;
    }
    
    //Reset the set of items if the loop has finished (so we can run it again
    //if necessary)
    $set = null;
    return false;
}

/**
 * @internal There is some serious duplication between this and loop_items().
 * It would be good to factor this out before release.
 * @todo Refactoring
 * @see loop_items()
 * @uses set_current_file()
 * @uses get_current_item()
 * @param boolean
 * @return boolean
 **/
function loop_files_for_item($reset=false)
{
    static $files = null;
    if(!$files) {
        $files = get_current_item()->Files;
    }
    
    if(list($key, $file) = each($files)) {
        set_current_file($file);
        return true;
    }
    
    //Reset loop at end
    $files = null;
    return false;
}

/**
 * @internal Duplication between this and set_current_item().  Factor into
 * separate
 * 
 * @access private
 * @param string
 * @return void
 **/
function set_current_file(File $file)
{
    __v()->file = $file;
}

/**
 * @access private
 * @return File
 **/
function get_current_file()
{
    return __v()->file;
}

function add_item_filter($field, $callback)
{
    
}

function add_filter()
{
    
}

/**
 * Return the pagination string.
 * 
 **/
function pagination($options = array('page'          => null, 
                                     'perPageCount'  => null, 
                                     'totalCount'    => null, 
                                     'pageName'      => null, 
                                     'url'           => null, 
                                     'queryArray'    => null, 
                                     'pagesType'     => null, 
                                     'displayFormat' => null, 
                                     'classes'       => null, 
                                     'texts'         => null))
{
    if (Zend_Registry::isRegistered('pagination')) {
		$p = Zend_Registry::get('pagination');
	}
    
	$page          = $options['page']          ? $options['page']          : $p['page'];
	$perPageCount  = $options['perPageCount']  ? $options['perPageCount']  : $p['per_page'];
	$totalCount    = $options['totalCount']    ? $options['totalCount']    : $p['total_results'];
    $pageName      = $options['pageName']      ? $options['pageName']      : false;
    $url           = $options['url']           ? $options['url']           : $p['link'];
    $queryArray    = $options['queryArray']    ? $options['queryArray']    : null;
    $pagesType     = $options['pagesType']     ? $options['pagesType']     : array('show' => 5);
    $displayFormat = $options['displayFormat'] ? $options['displayFormat'] : 2;
    $classes       = $options['classes']       ? $options['classes']       : array('pagination'    => '', 
                                                                                   'first'         => 'first', 
                                                                                   'firstGhost'    => '', 
                                                                                   'previous'      => 'previous', 
                                                                                   'previousGhost' => '', 
                                                                                   'ellipsis'      => '', 
                                                                                   'currentPage'   => 'current', 
                                                                                   'pages'         => '', 
                                                                                   'next'          => 'next', 
                                                                                   'nextGhost'     => '', 
                                                                                   'last'          => 'last', 
                                                                                   'lastGhost'     => '');
    $texts         = $options['texts']         ? $options['texts']         : array('first'    => 'First', 
                                                                                   'previous' => 'Previous', 
                                                                                   'ellipsis' => '&#8230;', 
                                                                                   'next'     => 'Next', 
                                                                                   'last'     => 'Last');
    
    return __v()->pagination($page, 
                             $perPageCount, 
                             $totalCount, 
                             array('pageName'     => $pageName, 
                                   'url'          => $url, 
                                   'queryArray'   => $queryArray, 
                                   'pagesType'    => $pagesType, 
                                   'displayFormat'=> $displayFormat, 
                                   'classes'      => $classes, 
                                   'texts'        => $texts));
}
