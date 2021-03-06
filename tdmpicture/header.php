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
 
include "../../mainfile.php";

/* Use XOOPS_ROOT_PATH for all include file */
include_once XOOPS_ROOT_PATH.'/class/pagenav.php';
include_once(XOOPS_ROOT_PATH."/class/tree.php");
include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
include_once XOOPS_ROOT_PATH.'/modules/'.$xoopsModule->getVar("dirname").'/include/common.php';
//include_once XOOPS_ROOT_PATH.'/modules/'.$xoopsModule->getVar("dirname").'/include/get_perms.php';
$gperm_handler =& xoops_gethandler('groupperm');
//permission
if (is_object($xoopsUser)) {
    $groups = $xoopsUser->getGroups();
	$xd_uid = $xoopsUser->getVar('uid');
} else {
	$groups = XOOPS_GROUP_ANONYMOUS;
	$xd_uid = 0;
}

//perm
if (!$gperm_handler->checkRight('tdmpicture_view', 2, $groups, $xoopsModule->getVar('mid'))) {
redirect_header(XOOPS_URL, 2,_MD_TDMPICTURE_NOPERM);
exit();
}

$perm_playlist = ($gperm_handler->checkRight('tdmpicture_view', 4, $groups, $xoopsModule->getVar('mid'))) ? true : false ;

$perm_vote = ($gperm_handler->checkRight('tdmpicture_view', 64, $groups, $xoopsModule->getVar('mid'))) ? true : false ;

$perm_submit = ($gperm_handler->checkRight('tdmpicture_view', 8, $groups, $xoopsModule->getVar('mid'))) ? true : false ;

$perm_cat = ($gperm_handler->checkRight('tdmpicture_view', 1024, $groups, $xoopsModule->getVar('mid'))) ? true : false ;

$perm_dl = ($gperm_handler->checkRight('tdmpicture_view', 32, $groups, $xoopsModule->getVar('mid'))) ? true : false ;

$mydirname = basename( dirname( __FILE__ ) ) ;
?>
