<?php
/**
 * ****************************************************************************
 *  - TDMPicture By TDM   - TEAM DEV MODULE FOR XOOPS
 *  - Licence PRO Copyright (c)  (http://www.tdmxoops.net)
 *
 * Cette licence, contient des limitations!!!
 *
 * 1. Vous devez poss�der une permission d'ex�cuter le logiciel, pour n'importe quel usage.
 * 2. Vous ne devez pas l' �tudier,
 * 3. Vous ne devez pas le redistribuer ni en faire des copies,
 * 4. Vous n'avez pas la libert� de l'am�liorer et de rendre publiques les modifications
 *
 * @license     TDMFR PRO license
 * @author		TDMFR ; TEAM DEV MODULE 
 *
 * ****************************************************************************
 */

if (!defined("XOOPS_ROOT_PATH")) {
    die("XOOPS root path not defined");
}

class TDMPicture_cat extends XoopsObject
{ 


// constructor
	function __construct()
	{
		$this->XoopsObject();
		$this->initVar("cat_id",XOBJ_DTYPE_INT,null,false,11);
		$this->initVar("cat_pid",XOBJ_DTYPE_INT,null,false,11);
		$this->initVar("cat_title",XOBJ_DTYPE_TXTBOX, null, false);
		$this->initVar("cat_date",XOBJ_DTYPE_INT,null,false,11);
		$this->initVar("cat_text",XOBJ_DTYPE_TXTAREA, null, false);
		$this->initVar("cat_img",XOBJ_DTYPE_TXTBOX, null, false);
		$this->initVar("cat_weight",XOBJ_DTYPE_INT,null,false,11);
		$this->initVar("cat_display",XOBJ_DTYPE_INT,null,false,1);
		$this->initVar("cat_uid",XOBJ_DTYPE_INT,null,false,11);
		$this->initVar("cat_index",XOBJ_DTYPE_INT,null,false,1);
        // Pour autoriser le html
        $this->initVar('dohtml', XOBJ_DTYPE_INT, 1, false);
	}

	  function TDMPicture_cat()
    {
        $this->__construct();
    }


    function getForm($action = false)
    {
	
 global $xoopsUser, $xoopsDB, $xoopsModule, $xoopsModuleConfig;
 
 if (is_object($xoopsUser)) {
    $groups = $xoopsUser->getGroups();
	$uid = $xoopsUser->getVar('uid');
} else {
	$groups = XOOPS_GROUP_ANONYMOUS;
	$uid = 0;
}
        if ($action === false) {
            $action = $_SERVER['REQUEST_URI'];
        }
        $title = $this->isNew() ? sprintf(_MD_TDMPICTURE_ADD) : sprintf(_MD_TDMPICTURE_EDIT);

        include_once(XOOPS_ROOT_PATH."/class/xoopsformloader.php");

        $form = new XoopsThemeForm($title, 'form', $action, 'post', true);
		$form->setExtra('enctype="multipart/form-data"');
        
		$form->addElement(new XoopsFormText(_MD_TDMPICTURE_TITLE, 'cat_title', 80, 255, $this->getVar('cat_title')));

		if (!$this->isNew()) {
            //Load groups
            $form->addElement(new XoopsFormHidden('id', $this->getVar('cat_id')));
		}

//categorie
		$cat_handler =& xoops_getModuleHandler('tdmpicture_cat', 'TDMPicture');
		
		$criteriaDisplay = new CriteriaCompo();
		$criteriaDisplay->add(new Criteria('cat_display', 1));
		$criteriaDisplay->add(new Criteria('cat_index', 1));

		$criteriaUser = new CriteriaCompo();
		$criteriaUser->add($criteriaDisplay);
		$criteriaUser->add(new Criteria('cat_display', 1), 'OR');
		$criteriaUser->add(new Criteria('cat_uid', $uid));

		$arr = $cat_handler->getall($criteriaUser);
		$mytree = new XoopsObjectTree($arr, 'cat_id', 'cat_pid');
		$form->addElement(new XoopsFormLabel(_MD_TDMPICTURE_PARENT, $mytree->makeSelBox('cat_pid', 'cat_title','-', $this->getVar('cat_pid'), true)));

//editor
	    $editor_configs=array();
    	$editor_configs["name"] = "cat_text";
    	$editor_configs["value"] = $this->getVar('cat_text', 'e');
    	$editor_configs["rows"] = 20;
    	$editor_configs["cols"] = 80;
    	$editor_configs["width"] = "100%";
    	$editor_configs["height"] = "400px";
    	$editor_configs["editor"] = $xoopsModuleConfig["tdmpicture_editor"];				
		$form->addElement( new XoopsFormEditor(_MD_TDMPICTURE_TEXT, "cat_text", $editor_configs), false );		

//upload
		$img = $this->getVar('cat_img') ? $this->getVar('cat_img') : 'blank.gif';
		$uploadirectory = "modules/".$xoopsModule->dirname()."/upload/cat/";
		$imgtray = new XoopsFormElementTray(_MD_TDMPICTURE_IMG,'<br />');
		$imgpath=sprintf(_MD_TDMPICTURE_PATH, $uploadirectory );
		$imageselect= new XoopsFormSelect($imgpath, 'img',$img);
		$topics_array = XoopsLists :: getImgListAsArray(XOOPS_ROOT_PATH."/".$uploadirectory);
		foreach( $topics_array as $image ) {
			$imageselect->addOption("$image", $image);
		}
		$imageselect->setExtra( "onchange='showImgSelected(\"image3\", \"img\", \"" . $uploadirectory . "\", \"\", \"" . XOOPS_URL . "\")'" );
		$imgtray->addElement($imageselect,false);
		$imgtray -> addElement( new XoopsFormLabel( '', "<br /><img src='" . XOOPS_URL . "/" . $uploadirectory . "/" . $img . "' name='image3' id='image3' alt='' />" ) );
	
		$fileseltray= new XoopsFormElementTray('','<br />');
		$fileseltray->addElement(new XoopsFormFile(_MD_TDMPICTURE_UPLOAD , 'attachedfile', $xoopsModuleConfig['tdmpicture_mimemax']),false);
		$fileseltray->addElement(new XoopsFormLabel(''), false);
		$imgtray->addElement($fileseltray);
		$form->addElement($imgtray);
		//
		
	//poit
	$form->addElement(new XoopsFormText(_MD_TDMPICTURE_WEIGHT, 'cat_weight', 10, 11, $this->getVar('cat_weight')));
		
			// Permissions
	$gperm_handler =& xoops_gethandler('groupperm');
	if (is_object($xoopsUser)) {
    $groups = $xoopsUser->getGroups();
	} else {
	$groups = XOOPS_GROUP_ANONYMOUS;
	}
    $member_handler = & xoops_gethandler('member');
    $group_list = &$member_handler->getGroupList();
    $gperm_handler = &xoops_gethandler('groupperm');
    $full_list = array_keys($group_list);
	
	if ($gperm_handler->checkRight('tdmpicture_view', 1048, $groups, $xoopsModule->getVar('mid'))) {
	
    if(!$this->isNew()) {		// Edit mode
    $groups_ids = $gperm_handler->getGroupIds('tdmpicture_catview', $this->getVar('cat_id'), $xoopsModule->getVar('mid'));
    $groups_ids = array_values($groups_ids);
    $groups_news_can_view_checkbox = new XoopsFormCheckBox(_MD_TDMPICTURE_PERM_2, 'groups_view[]', $groups_ids);
    } else {	// Creation mode
    $groups_news_can_view_checkbox = new XoopsFormCheckBox(_MD_TDMPICTURE_PERM_2, 'groups_view[]', $full_list);
    }
    $groups_news_can_view_checkbox->addOptionArray($group_list);
    $form->addElement($groups_news_can_view_checkbox);
	
	}
//	
		
		if ( is_object($xoopsUser) && $xoopsUser->isAdmin()) {
        $form->addElement(new XoopsFormRadioYN(_MD_TDMPICTURE_DISPLAYUSER, 'cat_display', $this->getVar('cat_display'), _YES, _NO));
		
	//$form->addElement(new XoopsFormRadioYN(_MD_TDMPICTURE_DISPLAYINDEX, 'cat_index', $this->getVar('cat_index'), _YES, _NO));
		
	$aff_index = new XoopsFormElementTray(_MD_TDMPICTURE_DISPLAYINDEX,'');
	$aff_index->setDescription(_MD_TDMPICTURE_DISPLAYINDEXDESC);
    $aff_index->addElement(new XoopsFormRadioYN('', 'cat_index', $this->getVar('cat_index'), _YES, _NO));
	 $form->addElement($aff_index);
		
		
		}else {
		
	if ($gperm_handler->checkRight('tdmpicture_view', 1048, $groups, $xoopsModule->getVar('mid'))) {
	        $form->addElement(new XoopsFormRadioYN(_MD_TDMPICTURE_DISPLAYUSER, 'cat_display', $this->getVar('cat_display'), _YES, _NO));
		$form->addElement(new XoopsFormRadioYN(_MD_TDMPICTURE_DISPLAYINDEX, 'cat_index', $this->getVar('cat_index'), _YES, _NO));
	}else {
	$form->addElement(new XoopsFormHidden('cat_display', 0));
	}
		}		
		$form->addElement(new XoopsFormHidden('op', 'save_cat'));
        $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

        return $form;
	}

}


class TDMPicturetdmpicture_catHandler extends XoopsPersistableObjectHandler 
{

    function __construct(&$db) 
    {
        parent::__construct($db, "tdmpicture_cat", 'TDMPicture_cat', 'cat_id', 'cat_title');
    }

}


?>