<?php
//include
include_once "header.php";
$myts =& MyTextSanitizer::getInstance();
//$xoopsOption['template_main'] = 'tdmmovie_movie.html';
include_once XOOPS_ROOT_PATH.'/header.php';
include_once(XOOPS_ROOT_PATH.'/modules/'.$xoopsModule->dirname().'/include/common.php');

$gperm_handler =& xoops_gethandler('groupperm');
//permission
if (is_object($xoopsUser)) {
    $groups = $xoopsUser->getGroups();
	$uid = $xoopsUser->getVar('uid');
} else {
	$groups = XOOPS_GROUP_ANONYMOUS;
	$uid = 0;
}

   //perm
if (!$gperm_handler->checkRight('tdmpicture_view', 8, $groups, $xoopsModule->getVar('mid')) && !$gperm_handler->checkRight('tdmpicture_view', 16, $groups, $xoopsModule->getVar('mid'))) {
redirect_header(XOOPS_URL, 2,_MD_TDMPICTURE_NOPERM);
exit();
}
//load class
$file_handler =& xoops_getModuleHandler('tdmpicture_file', 'TDMPicture');
$cat_handler =& xoops_getModuleHandler('tdmpicture_cat', 'TDMPicture');

$op = isset($_REQUEST['op']) ? $_REQUEST['op'] : 'upload';

global $xoopsUser, $xoopsModule, $xoopsModuleConfig;

 switch($op) {
 
  case "upload":
  
  //perm
if (!$gperm_handler->checkRight('tdmpicture_view', 8, $groups, $xoopsModule->getVar('mid')) && !$gperm_handler->checkRight('tdmpicture_view', 16, $groups, $xoopsModule->getVar('mid'))) {
redirect_header(XOOPS_URL, 2,_MD_TDMPICTURE_NOPERM);
exit();
}else {
  		// Affichage du formulaire de cr?ation de cat?gories
    	$obj =& $file_handler->create();
    	$form = $obj->getForm();
    	$form->display();
		}
		break;	

  case "cat":
  
  //perm
if (!$gperm_handler->checkRight('tdmpicture_view', 1024, $groups, $xoopsModule->getVar('mid')) && !$gperm_handler->checkRight('tdmpicture_view', 1048, $groups, $xoopsModule->getVar('mid'))) {
redirect_header(XOOPS_URL, 2,_MD_TDMPICTURE_NOPERM);
exit();
}else {
  		// Affichage du formulaire de cr?ation de cat?gories
		$obj =& $cat_handler->create();
		$form = $obj->getForm();
    	$form->display();
		}
		break;	

case "save_cat":
 
		if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header('submit.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
		}
		if (isset($_REQUEST['cat_id'])) {
        $obj =& $cat_handler->get($_REQUEST['cat_id']);
		} else {
        $obj =& $cat_handler->create();
		}   
	
	//upload	
	include_once XOOPS_ROOT_PATH.'/class/uploader.php';	
	$uploaddir = TDMPICTURE_CAT_PATH;
	$mimetype = explode('|',$xoopsModuleConfig['tdmpicture_mimetype']);
    $uploader = new XoopsMediaUploader($uploaddir, $mimetype, $xoopsModuleConfig['tdmpicture_mimemax']);

		if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
		$uploader->setPrefix('picture_') ;
		$uploader->fetchMedia($_POST['xoops_upload_file'][0]);
		if (!$uploader->upload()) {
		$errors = $uploader->getErrors();
		redirect_header("index.php",2, $errors);
		} else {
		$obj->setVar('cat_img', $uploader->getSavedFileName());
		}
		} else {
		$obj->setVar('cat_img', $_REQUEST['img']);
		}
	//
		$obj->setVar('cat_pid', $_REQUEST['cat_pid']);
		$obj->setVar('cat_title', $_REQUEST['cat_title']);
		$obj->setVar('cat_text', $_REQUEST['cat_text']);
		$obj->setVar('cat_weight', $_REQUEST['cat_weight']);
		$obj->setVar('cat_display', $_REQUEST['cat_display']);
		$obj->setVar('cat_uid', $uid);
		$obj->setVar('cat_index', $_REQUEST['cat_index']);

		if ($cat_handler->insert($obj)) {
	 
	//permission
	$perm_id = isset($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : $obj->getVar('cat_id');
	$gperm_handler = &xoops_gethandler('groupperm');
	$criteria = new CriteriaCompo();
	$criteria->add(new Criteria('gperm_itemid', $perm_id, '='));
	$criteria->add(new Criteria('gperm_modid', $xoopsModule->getVar('mid'),'='));
	$criteria->add(new Criteria('gperm_name', 'tdmpicture_catview', '='));
	$gperm_handler->deleteAll($criteria);

	if(isset($_POST['groups_view'])) {
		foreach($_POST['groups_view'] as $onegroup_id) {
			$gperm_handler->addRight('tdmpicture_catview', $perm_id, $onegroup_id, $xoopsModule->getVar('mid'));
		}
	}
	
        redirect_header('submit.php', 2, _MD_TDMPICTURE_BASE);
		}
		//include_once('../include/forms.php');
		echo $obj->getHtmlErrors().$errors;
		$form =& $obj->getForm();
		$form->display();
    break;		

	case "save_file":
 
		if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header('files.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
		}
		
		if (isset($_REQUEST['file_id'])) {
        $obj =& $file_handler->get($_REQUEST['file_id']);
		} else {
        $obj =& $file_handler->create();
		}
		
		//include	
		include_once XOOPS_ROOT_PATH.'/class/uploader.php';
		include_once(TDMPICTURE_ROOT_PATH."/class/thumbnail.inc.php");
		//prepare l'upload
		$path = $obj->getFilePath();
		@chmod ($path['image_path'], 0755);
		$mimetype = explode('|',$xoopsModuleConfig['tdmpicture_mimetype']);
		$uploader = new XoopsMediaUploader($path['image_path'], $mimetype, $xoopsModuleConfig['tdmpicture_mimemax'], null, null);
		
        $obj =& $file_handler->create(); 
		//variable commune
		$obj->setVar('file_cat', $_REQUEST['file_cat']);
		$obj->setVar('file_display', $_REQUEST['file_display']);
		$obj->setVar('file_indate', time());
		$obj->setVar('file_uid', !empty($xoopsUser) ? $xoopsUser->getVar('uid') : 0);
		//$obj->setVar('file_ext', $_REQUEST['file_ext']);
//upload	

foreach ($_FILES['tdmfile']['error'] as $key => $value) 
{ 
	
	if ($uploader->fetchMedia('tdmfile',$key)) {
		$uploader->setPrefix('picture_') ;
		$uploader->fetchMedia('tdmfile',$key);
		if (!$uploader->upload()) {
		$errors = $uploader->getErrors();
		redirect_header("index.php",2, $errors);
		} else {
		$erreur=true;
		if (strrpos($uploader->getMediaName(), '.') !== false) {
         $ext = substr($uploader->getMediaName(), 0, strrpos($uploader->getMediaName(), "."));
		} else {
		$ext = $uploader->getMediaName();
		}
		
		$file_path = $obj->getFilePath($uploader->getSavedFileName());
		
		$photo = new Thumbnail($file_path['image_path']);
		$obj->setVar('file_title', $ext);
		$obj->setVar('file_file', $uploader->getSavedFileName());
		$obj->setVar('file_type', $uploader->getMediaType());
		//redimention image 
		if (!empty($_REQUEST['resize'])) {
		$size = explode('x',$_REQUEST['resize']);	
		$photo->adaptiveResize($size[0], $size[1]);
		$photo->save($file_path['image_path'],$xoopsModuleConfig['tdmpicture_thumb_quality']);
		}	
		
		$obj->setVar('file_res_x', $photo->getCurrentWidth());
		$obj->setVar('file_res_y', $photo->getCurrentHeight());
		$obj->setVar('file_size', $photo->getCurrentSize());
		
		//thumb
		$file_handler->thumb('', $uploader->getSavedFileName());
		
		$erreur = $file_handler->insert($obj);
		}
	} 
}
		if ($erreur) {
        redirect_header('submit.php', 2, _MD_TDMPICTURE_BASE);
		}else {
		$uploader->getErrors();
		}
		//include_once('../include/forms.php');
		echo $obj->getHtmlErrors().$errors;
		$form =& $obj->getForm();
		$form->display();
    break;
	
	
  case "list": 
  default:
  	redirect_header("index.php", 2, _MD_TDMPICTURE_NOPERM);
	exit();		
	  } 
tdmpicture_header();	  
include_once "../../footer.php";
?>