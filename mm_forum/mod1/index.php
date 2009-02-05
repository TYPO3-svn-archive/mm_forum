<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Mittwald CM Service
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  113: class  tx_mmforum_module1 extends t3lib_SCbase
 *
 *              SECTION: Basic backend module functions
 *  130:     function init()
 *  147:     function menuConfig()
 *  172:     function main()
 *  200:     function jumpToUrl(URL)
 *  256:     function printContent()
 *  267:     function moduleContent()
 *  324:     function noSettingsError()
 *
 *              SECTION: Content functions
 *  343:     function userManagement()
 *  461:     function forumManagement()
 *  472:     function editTemplates()
 *  484:     function Tools()
 *  519:     function BBCodes()
 *  605:     function Smilies()
 *  697:     function SyntaxHL()
 *  809:     function getFileOptionFields($path,$fileExt,$opVar = '',$noDel)
 *  828:     function Import()
 *  840:     function Install()
 *  853:     function UserFields()
 *
 *              SECTION: Miscellaneous helper functions
 *  872:     function linkParams($arr)
 *  886:     function getForenCount()
 *  901:     function feGroups2Array()
 *  918:     function getItemFromRecord($table,$row)
 *  947:     function convertToTCEList($list,$table,$fieldname)
 *
 *              SECTION: Configuration variable management
 *  974:     function loadConfVars()
 * 1003:     function setConfVar($elem,$value)
 * 1031:     function parseConf($conf=FALSE,$ind=0)
 * 1053:     function getIsConfigured()
 * 1066:     function getInd($ind)
 *
 * TOTAL FUNCTIONS: 28
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

	// DEFAULT initialization of a module [BEGIN]

unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

define("BACK_PATH",$BACK_PATH);

require_once('./class.tx_mmforum_import.php');
require_once('./class.tx_mmforum_phpbbimport.php');
require_once('./class.tx_mmforum_chcimport.php');
require_once('./class.tx_mmforum_templates.php');
require_once('./class.tx_mmforum_install.php');
require_once('./class.tx_mmforum_userfields.php');
require_once('./class.tx_mmforum_forumadmin.php');
require_once('./class.tx_mmforum_statistics.php');
require_once('./class.tx_mmforum_ranksbe.php');
require_once('./class.tx_mmforum_updater.php');

$LANG->includeLLFile('EXT:mm_forum/mod1/locallang.xml');

require_once(PATH_t3lib.'class.t3lib_scbase.php');
require_once(PATH_t3lib.'class.t3lib_tceforms.php');
require_once(PATH_t3lib.'class.t3lib_tsparser.php');

$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * The 'mmforum_admin' module for the 'mm_forum' extension.
 * This module is intended for backend administration of the forum and
 * offers functions for user administration, board and category
 * configuration, template editing, bb code and smilie configuration
 * and some other features.
 *
 * @author     Steffen Kamper <steffen@sk-typo3.de>
 * @author     Martin Helmich <m.helmich@mittwald.de>
 * @author     Björn Detert <b.detert@mittwald.de>
 * @package    mm_forum
 * @subpackage Backend
 * @copyright  2008 Mittwald CM Service
 * @version    2008-04-20
 */
class  tx_mmforum_module1 extends t3lib_SCbase {
	var $pageinfo;
    var $confArr;
    var $display_mmLogo=FALSE;
    
    var $tceforms;
    var $configFile;
    
    /**
     * Basic backend module functions
     */
    
	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				 '1' => $LANG->getLL('menu.useradmin'),
				 '2' => $LANG->getLL('menu.boardadmin'),
				 '3' => $LANG->getLL('menu.template'),
				 '4' => $LANG->getLL('menu.tools'),
				 '5' => $LANG->getLL('menu.import'),
                 '6' => $LANG->getLL('menu.userFields'),
                 '7' => $LANG->getLL('menu.install'),
				 '8' => $LANG->getLL('menu.statistics'),
				 '9' => $LANG->getLL('menu.ranks'),
				'10' => $LANG->getLL('menu.updater'),
                #'7' => $LANG->getLL('menu.importexport'),
                #'8' => $LANG->getLL('menu.documentation')
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content.
	 * @return void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TBE_STYLES;
        
        $TBE_STYLES['stylesheet2']=t3lib_extMgm::extRelPath('mm_forum').'mod1/css/style.css'; 
        $this->logo='<div><img src="css/header.jpg" width="733" height="80" alt="logo" title="powered by mittwald" /></div>'; 
        $this->configFile = PATH_typo3conf.'../typo3conf/tx_mmforum_config.ts';
        
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		
		$this->id = 1;
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		#$this->confArr= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mm_forum']);
        
        $access = 1;
		#if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
        if ($access || $BE_USER->user['admin'])	{
        
            $this->loadConfVars();
            if(!$this->getIsConfigured()) unset($this->MOD_MENU['function']);
        
				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" name="editform" method="POST">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content .= $this->doc->divider(5);

			$this->tceforms = t3lib_div::makeInstance("t3lib_TCEforms");
			$this->tceforms->backPath = $BACK_PATH;
			
			$this->content .= $this->tceforms->printNeededJSFunctions_top();
			
			// Render content:
			$this->moduleContent();
            $content = $this->content;
			
			$this->content .= $this->tceforms->printNeededJSFunctions();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content .= $this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content .= $this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		global $LANG;
        $content = $this->display_mmLogo?$this->logo:'';
        
        #if(intval($this->confArr['userPID'])==0 || intval($this->confArr['forumPID'])==0 ||
        #   intval($this->confArr['userGroup'])==0 || intval($this->confArr['modGroup'])==0 || intval($this->confArr['adminGroup'])==0 ) {
        if(!$this->getIsConfigured()) {
            #$content .= $this->noSettingsError();
			#$this->content.=$this->doc->section($LANG->getLL('error').':',$content,0,1);
			
            $content .= $this->Install();
            $this->content .= $this->doc->section($LANG->getLL('menu.install').':',$content,0,1);
            
			return;
        }
        
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content.=$this->userManagement();
				$this->content.=$this->doc->section($LANG->getLL('menu.useradmin').':',$content,0,1);
			break;
			case 2:
				$content.=$this->forumManagement();
				$this->content.=$this->doc->section($LANG->getLL('menu.boardadmin').':',$content,0,1);
			break;
			case 3:
				$content.=$this->editTemplates();
				$this->content.=$this->doc->section($LANG->getLL('menu.template').':',$content,0,1);
			break;
            case 4:
				$content.=$this->Tools();
				$this->content.=$this->doc->section($LANG->getLL('menu.tools').':',$content,0,1);
			break;
            case 5:
				$content.=$this->Import();
				$this->content.=$this->doc->section($LANG->getLL('menu.import').':',$content,0,1);
			break;
            case 6:
				$content.=$this->UserFields(); 
				$this->content.=$this->doc->section($LANG->getLL('menu.userFields').':',$content,0,1);
			break;
			case 7:
				$content.=$this->Install();
				$this->content.=$this->doc->section($LANG->getLL('menu.install').':',$content,0,1);
			break;
            case 8:
				$content.=$this->Statistics(); 
				$this->content.=$this->doc->section($LANG->getLL('menu.statistics').':',$content,0,1);
			break;
            case 9:
                $content .= $this->Ranks();
				$this->content.=$this->doc->section($LANG->getLL('menu.ranks').':',$content,0,1);
            break;
            case 10:
            	$content .= $this->Updater();
				$this->content.=$this->doc->section($LANG->getLL('menu.updater').':',$content,0,1);
			break;
		}
	}
	
	function noSettingsError() {
		global $LANG;
		$content = $LANG->getLL('error.noSettings');
		
		return $content;
	}
	
    /**
     * Content functions
     */
    
	/**
	 * Displays the user administration interface.
	 * This includes a list of all registered users ordered descending by
	 * username. The list includes the usergroups a user is member in and the
	 * user's age. A search function is also included.
	 * 
	 * @return string The HTML output.
	 */
	function userManagement() {
		
		// Retrieve global variables
			global $LANG;
		
		// Generate SQL query
			$ug=$this->feGroups2Array();
			$mmforum=t3lib_div::_GP('mmforum');
			if($mmforum['no_filter']) {unset($mmforum['sword']);unset($mmforum['old_sword']);}
			if($mmforum['old_sword'] && !$mmforum['sword']) $mmforum['sword']=$mmforum['old_sword']  ;
			if($mmforum['sword']) $gp='&mmforum[sword]='.$mmforum['sword'];
			
			$groups	= implode(',',array(intval($this->confArr['userGroup']),intval($this->confArr['modGroup']),intval($this->confArr['adminGroup'])));
			$filter	= $mmforum['sword']?"username like '".mysql_escape_string($mmforum['sword'])."%'":'1';
            $order	= 'username '.$orderBy.'';
            if(t3lib_div::GPvar('mmforum_sort') == 'username'){
                $orderBy	= mysql_escape_string(t3lib_div::GPvar('mmforum_style'));
                $order		= 'username '.$orderBy.'';
            }
            if(t3lib_div::GPvar('mmforum_sort') == 'age'){
                $orderBy	= mysql_escape_string(t3lib_div::GPvar('mmforum_style'));
                $order		= 'crdate '.$orderBy.'';
            }
			
            #$userGroup_query = "(".$this->confArr['userGroup']." IN (usergroup) OR ".$this->confArr['modGroup']." IN (usergroup) OR ".$this->confArr['adminGroup']." IN (usergroup))";
            $userGroup_query = "(FIND_IN_SET('".$this->confArr['userGroup']."',usergroup) OR FIND_IN_SET('".$this->confArr['modGroup']."',usergroup) OR FIND_IN_SET('".$this->confArr['adminGroup']."',usergroup))";
            #$userGroup_query = "1";
            $res	= $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)','fe_users',"$filter and pid='".$this->confArr['userPID']."' and ".$userGroup_query." and deleted=0"); 
			$row	= $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			$records= $row[0];
			$pages	= ceil($records/$this->confArr['recordsPerPage']);
			$offset	= intval($mmforum['offset']);

		// Page navigation
			$pb		= $LANG->getLL('page.page').' <a href="index.php?mmforum[offset]=0'.$gp.'">['.$LANG->getLL('page.first').']</a> ';
			$end	= $offset+6>=$pages ?$pages:$offset+6;
			$start	= $offset-5;
			if($start<0) $start=0;
			if($start>0)$pb.='... ';     
			for($i=$start; $i<$end; $i++) {
				$pb	.= '<a href="index.php?mmforum[offset]='.$i.$gp.'">'.($i==$offset ? '<b>'.($i+1).'</b>':($i+1)).'</a> ';
			}
			if($offset+11<$pages) $pb.=' ... <a href="index.php?mmforum[offset]='.($pages-1).$gp.'">['.$LANG->getLL('page.last').']</a> ';

		// Generate header table
			if($records < $this->confArr['recordsPerPage']) $mDisp = $records; else $mDisp = ($offset*$this->confArr['recordsPerPage']+$this->confArr['recordsPerPage']);
            $userString = sprintf($LANG->getLL('useradmin.usercount'),($offset*$this->confArr['recordsPerPage']+1),$mDisp,$records);

			$out .= '<table width="733"><tr>';
			$out .= '<td width="420">'.$pb.'</td>';
			$out .= '<td width="120" align="center"><b>'.$userString.'</b></td>';
			$out .= '<td align="right">'.$LANG->getLL('useradmin.searchfor').': <input type="text" id="sword" size="20" name="mmforum[sword]" /></td>';
			$out .= '</tr></table>';

			if($mmforum['sword'] || $mmforum['old_sword']) {
				$out .= '<p>'.$LANG->getLL('useradmin.filter').': '.$mmforum['sword'].'*               <a href="index.php?mmforum[no_filter]=1&'.$this->linkParams($mmforum).'">'.$LANG->getLL('useradmin.filter.clear').'</a></p>'; 
				$out .= '<input type="hidden" name="mmforum[old_sword]" value="'.$mmforum['sword'].'" />';
			}
            
		// Display userdata table
			// Execute database query
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users',"$filter and pid='".$this->confArr['userPID']."' and deleted=0 AND ".$userGroup_query,'',$order,($offset*$this->confArr['recordsPerPage']).",".$this->confArr['recordsPerPage']);
			if($res)
			{
				$out .= '<div class="mm_forum-list" style="overflow:auto;">';
				
				$out .= '<div class="mm_forum-listrow_header" >
						    <div class="mm_forum-listrow-edit"></div>
						    <div class="mm_forum-listrowheader-username">'.$LANG->getLL('useradmin.username').'&nbsp;&nbsp;';
                $out .= '<a href="index.php?mmforum_sort=username&mmforum_style=asc"><img src="css/arrow_down.gif" alt="asc" title="asc" /></a>&nbsp;&nbsp;&nbsp;<a href="index.php?mmforum_sort=username&mmforum_style=desc"><img src="css/arrow_up.gif" alt="desc" title="desc" /></a></div>
						    <div class="mm_forum-listrowheader-age">'.$LANG->getLL('useradmin.age').'&nbsp;&nbsp;';
                $out .= '<a href="index.php?mmforum_sort=age&mmforum_style=asc"><img src="css/arrow_down.gif" alt="asc" title="asc" /></a>&nbsp;&nbsp;&nbsp;<a href="index.php?mmforum_sort=age&mmforum_style=desc"><img src="css/arrow_up.gif" alt="desc" title="desc" /></a></div>
						    <div class="mm_forum-listrowheader-groups">'.$LANG->getLL('useradmin.usergroup').'</div>
						 </div>';
				$i=0;                                   
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					// Display user groups
						$g = explode(',',$row['usergroup']);
						$outg='';
						foreach($g as $sg) {$outg.=$ug[$sg].', ';}
				
					// Generate link to the record editing page
						global $BACK_PATH,$LANG,$TCA,$BE_USER;

						$iconAltText	= t3lib_BEfunc::getRecordIconAltText($row,$table);
						$elementTitle	= t3lib_BEfunc::getRecordPath($row['uid'],'1=1',0);
						$elementTitle	= t3lib_div::fixed_lgd_cs($elementTitle,-($BE_USER->uc['titleLen']));
						$elementIcon	= t3lib_iconworks::getIconImage($table,$row,$BACK_PATH,'class="c-recicon" title="'.$iconAltText.'"');
						
						$params = '&edit[fe_users]['.$row['uid'].']=edit';
						$editOnClick = t3lib_BEfunc::editOnClick($params,$BACK_PATH);
					
					// Generate row item
						$class_suffix = ($i++ % 2==0 ? '2' : '');
						$link = "index.php?mmforum[cid]=".$row['uid'];
						$js = 'onmouseover="this.className=\'mm_forum-listrow_active\'; this.style.cursor=\'pointer\';" onmouseout="this.className=\'mm_forum-listrow'.$class_suffix.'\'" onclick="'.htmlspecialchars($editOnClick).'"';
						$icon = '<img src="../icon_tx_mmforum_forums.gif" />';
						$hidden = ($row['hidden']==1?'<span style="color:blue;">['.$LANG->getLL('boardadmin.hidden').']</span> ':'');
						
						$out .= '<div '.$js.' class="mm_forum-listrow'.$class_suffix.'" style="overflow:visible;">';
						$out .= '<div class="mm_forum-listrow-edit">'.$this->getItemFromRecord('fe_users',$row).'</div>';
						$out .= '<div class="mm_forum-listrow-username">'.$row['username'].'</div>';
						$out .= '<div class="mm_forum-listrow-age">'.t3lib_BEfunc::dateTimeAge($row['crdate'],1).'</div>';
						$out .= '<div class="mm_forum-listrow-groups">'.(substr($outg,-2)==', '?substr($outg,0,strlen($outg)-2):$outg).'</div>';
						$out .= '</div>';
				}
				
				$out .= '</div>';
			}
			
		return $out;
	}
	
	/**
	 * Displays the message board administration user interface. This displays
	 * a list of all categories and all subordinate message boards and offers
	 * editing and creation functions.
	 * 
	 * @return string The HTML user interface
	 */
	function forumManagement() {
        $forumAdmin = t3lib_div::makeInstance('tx_mmforum_forumAdmin');
        $forumAdmin->p = $this;
        return $forumAdmin->main('');
	}

	/**
	 * Displays a form for editing template files directly in the browser.
	 * @return string The HTML user interface
	 */
	function editTemplates() {
        $template = t3lib_div::makeInstance('tx_mmforum_templates');
        $template->p = $this;
        return $template->main('');
	}

	/**
	 * This function displays the "tools" menu of the mm_forum backend
	 * administration. This includes bb code and smilie editing.
	 * @return string The HTML user interface
	 */
	function Tools() {

		global $LANG;

		$mmforum=t3lib_div::_GP('mmforum'); 
		
		// Output tools menu
		$content .= '<div><a href="index.php?mmforum[tools]=1" '.($mmforum['tools']==1 ? 'class="activ"':'').'>'.$LANG->getLL('tools.bbcodes').'</a>&nbsp;|&nbsp;';
		$content .= '<a href="index.php?mmforum[tools]=2" '.($mmforum['tools']==2 ? 'class="activ"':'').'>'.$LANG->getLL('tools.smilies').'</a>&nbsp;|&nbsp;';
		$content .= '<a href="index.php?mmforum[tools]=3" '.($mmforum['tools']==3 ? 'class="activ"':'').'>'.$LANG->getLL('tools.syntaxhighlighter').'</a>&nbsp;|&nbsp;';
		$content .= '</div><hr>';
		$content .= '<input type="hidden" name="mmforum[tools]" value="'.$mmforum['tools'].'" />';

		switch ($mmforum['tools']) {
			case 1: // BB-Codes
				$content.=	$this->BBCodes();
			break;
			case 2: // Smilies
				$content.=	$this->Smilies();
			break;
			case 3: // Syntax HighLighting
				$content.=	$this->SyntaxHL();
			break;

		}

		return $content;
	}

	/**
	 * Displays a form for editing the bb codes that are to be used in the forum.
	 * All bb codes and their regarding replacement patterns are stored in a database table.
	 * @return string The HTML user interface
	 */
	function BBCodes() {
		
		global $LANG;
		
		$mmforum=t3lib_div::_GP('mmforum'); 

		// Process submitted data
		if(isset($mmforum['delete'])) {
			$key = key($mmforum['delete']);
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_postparser','uid='.$key,array('deleted'=>1));
		}
		if(isset($mmforum['hide'])) {
			$key = key($mmforum['hide']);
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_postparser','uid='.$key,array('hidden'=>1));
		}
		if(isset($mmforum['unhide'])) {
			$key = key($mmforum['unhide']);
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_postparser','uid='.$key,array('hidden'=>0));
		}
		if(isset($mmforum['save'])) {
			$key = key($mmforum['save']);
			if($key==0) {
				$insertArr = array(
					'crdate'		=> time(),
					'tstamp'		=> time(),
					'bbcode'		=> $mmforum['bbcode'][0],
					'pattern'		=> $mmforum['pattern'][0],
					'replacement'	=> $mmforum['replacement'][0], 
				);
				$res=$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'tx_mmforum_postparser',
					$insertArr
				);
				$mmforum['bbcode'][0]=$mmforum['pattern'][0]=$mmforum['replacement'][0]='';
			} else {
				$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_postparser','uid='.$key,array(
					'bbcode'		=>$mmforum['bbcode'][$key],
					'pattern'		=>$mmforum['pattern'][$key],
					'replacement'	=>$mmforum['replacement'][$key]
				));
			}
		}

		// Display bb code editing form
		$i=0;
		$content .= '<table cellpadding="2" cellspacing="0" width="100%" class="mm_forum-list">';
		$content .= '<tr>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.bbcode').'</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.pattern').'</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.replacement').'</td>
						<td class="mm_forum-listrow_header"></td>
					</tr>';
		$content .= '<tr>
						<td class="mm_forum-listrow2"><input type="text" name="mmforum[bbcode][0]" value="'.$mmforum['bbcode'][0].'" size="24" /></td>
						<td class="mm_forum-listrow2"><input type="text" name="mmforum[pattern][0]" value="'.$mmforum['pattern'][0].'" size="40" /></td>
						<td class="mm_forum-listrow2"><input type="text" name="mmforum[replacement][0]" value="'.htmlspecialchars($mmforum['replacement'][0]).'" size="40" /></td>
						<td class="mm_forum-listrow2" align="right"><input style="border:0px;" type="image" name="mmforum[save][0]" src="../../../../typo3/sysext/t3skin/icons/gfx/savedok.gif" /></td>
					</tr>';
		
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_mmforum_postparser','deleted=0','','bbcode asc');

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= (!$row['hidden']) ? '<tr class="mm_forum-listrow'.($i++ % 2==0 ? '' : '2').'">' : '<tr class="hidden">';
			$content .= '	<td><input type="text" name="mmforum[bbcode]['.$row['uid'].']" value="'.htmlspecialchars($row['bbcode']).'" size="24" /></td>';
			$content .= '	<td><input type="text" name="mmforum[pattern]['.$row['uid'].']" value="'.htmlspecialchars($row['pattern']).'" size="40" /></td>';
			$content .= '	<td><input type="text" name="mmforum[replacement]['.$row['uid'].']" value="'.htmlspecialchars($row['replacement']).'" size="40" /></td>';       
			$content .= '	<td align="right">';
			$content .= '		<a href="index.php?mmforum[tools]=1&mmforum[delete]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/garbage.gif" /></a>&nbsp;&nbsp;';
			$content .= 		$row['hidden']?'<a href="index.php?mmforum[tools]=1&mmforum[unhide]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/button_unhide.gif" /></a>&nbsp;&nbsp;':'<a href="index.php?mmforum[tools]=1&mmforum[hide]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/button_hide.gif" /></a>&nbsp;&nbsp;';
			$content .= '		<input style="border:0px;" type="image" src="../../../../typo3/sysext/t3skin/icons/gfx/savedok.gif" name="mmforum[save]['.$row['uid'].']" />';
			$content .= '	</td>';
			$content .= '</tr>';
		} 
		
		$content .= '</table>';
		
		return $content;
	}

	/**
	 * Displays a form for editing smilies that are to be used in the forum.
	 * A smilie each consists of a image and a short tag, like :) or :(,
	 * that is substituted with the image.
	 * @return string The HTML user interface
	 */
	function Smilies() {

		global $LANG;

		$mmforum=t3lib_div::_GP('mmforum'); 

		// Process submitted data
		if(isset($mmforum['delete'])) {
			$key=key($mmforum['delete']);
			$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_smilies','uid='.$key,array('deleted'=>1));
		}
		if(isset($mmforum['hide'])) {
			$key=key($mmforum['hide']);
			$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_smilies','uid='.$key,array('hidden'=>1));
		}
		if(isset($mmforum['unhide'])) {
			$key=key($mmforum['unhide']);
			$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_smilies','uid='.$key,array('hidden'=>0));
		}
		if(isset($mmforum['save'])) {
			$key=key($mmforum['save']);
			if($key==0) {
				$insertArr = array(
					'crdate' => time(),
					'tstamp' => time(),
					'smile_url' => $mmforum['new']['smile_url'],
					'code' => $mmforum['new']['code'],
				);
				$res=$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'tx_mmforum_smilies',
					$insertArr
				);
			}
			else {
				$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_smilies','uid='.$key,array('code'=>$mmforum['code'][$key]));
			}
		}

		// Display smilie editing form
		$path=t3lib_extMgm::extPath('mm_forum').'/res/smilies/';
		$files=t3lib_div::getFilesInDir($path,'gif');
		$firstFile='';
		if(count($files)>0) {
			foreach($files as $k=>$f) {
				if($firstFile=='')$firstFile=$f; 
				$surlOptions.='<option value="'.$f.'"'.($mmforum['new']['smile_url']==$f?'selected="selected"':'').'>'.$f.'</option>'; 
			}
		}

		$i=0;
		if(!isset($mmforum['new']['smile_url']))  $mmforum['new']['smile_url']=$firstFile;
		
		$content .= '<table cellpadding="2" cellspacing="0" class="mm_forum-list" width="100%">';
		$content .= '<tr>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.code').'</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.smilie').'</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.file').'</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.editcode').'</td>
						<td class="mm_forum-listrow_header">&nbsp;</td>
					</tr>';
		$content .= '<tr>
						<td class="mm_forum-listrow"><b>['.$LANG->getLL('tools.new').']</b></td>
						<td class="mm_forum-listrow"><img src="../res/smilies/'.htmlspecialchars($mmforum['new']['smile_url']).'" id="smilie_preview" /></td>
						<td class="mm_forum-listrow"><select name="mmforum[new][smile_url]" onchange="document.getElementById(\'smilie_preview\').src=\'../res/smilies/\'+this[this.selectedIndex].value">'.$surlOptions.'</select></td>
						<td class="mm_forum-listrow"><input type="text" name="mmforum[new][code]" value="'.htmlspecialchars($mmforum['new']['code']).'" /></td>
						<td align="right" class="mm_forum-listrow"><input type="image" src="../../../../typo3/sysext/t3skin/icons/gfx/savedok.gif" name="mmforum[save][0]" /></td>
					</tr>';
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_mmforum_smilies','deleted=0','','smile_url asc');

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= (!$row['hidden']) ? '<tr class="mm_forum-listrow'.($i++ % 2==0 ? '' : '2').'">' : '<tr class="hidden">';
			$content .= '	<td>'.$row['code'].'</td>';
			$content .= '	<td><img src="../res/smilies/'.$row['smile_url'].'" /></td>';
			$content .= '	<td>'.$row['smile_url'].'</td>';
			$content .= '	<td><input type="text" name="mmforum[code]['.$row['uid'].']" value="'.$row['code'].'" /></td>';       
			$content .= '	<td align="right">';
			$content .= '		<a href="index.php?mmforum[tools]=2&mmforum[delete]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/garbage.gif" /></a>&nbsp;&nbsp;';
			$content .= 		$row['hidden']?'<a href="index.php?mmforum[tools]=2&mmforum[unhide]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/button_unhide.gif" /></a>&nbsp;&nbsp;':'<a href="index.php?mmforum[tools]=2&mmforum[hide]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/button_hide.gif" /></a>&nbsp;&nbsp;';
			$content .= '		<input style="border:0px;" type="image" src="../../../../typo3/sysext/t3skin/icons/gfx/savedok.gif" name="mmforum[save]['.$row['uid'].']" />';
			$content .= '	</td>';
			$content .= '</tr>';
		}
		$content.='</table>';
		return $content;
	}
	
	/**
	 * Displays a form for editing syntaxhighlghtinh parser options that are to be used in the forum.
	 *@return string The HTML user interface
	 */
	function SyntaxHL() {

		global $LANG;

		$mmforum=t3lib_div::_GP('mmforum'); 

		// Process submitted data
		if(isset($mmforum['delete'])) {
			$key=key($mmforum['delete']);
			$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_syntaxhl','uid='.$key,array('deleted'=>1));
		}
		if(isset($mmforum['hide'])) {
			$key=key($mmforum['hide']);
			$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_syntaxhl','uid='.$key,array('hidden'=>1));
		}
		if(isset($mmforum['unhide'])) {
			$key=key($mmforum['unhide']);
			$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_syntaxhl','uid='.$key,array('hidden'=>0));
		}
		if(isset($mmforum['save'])) {
			$key=key($mmforum['save']);
			if($key==0) {
				$insertArr = array(
					'crdate' 		=>	time(),
					'tstamp' 		=>	time(),
					'lang_title' 	=>	$mmforum['new']['lang_title'],
					'lang_pattern' 	=>	$mmforum['new']['lang_pattern'],
					'lang_code'		=>	$mmforum['new']['lang_code'],
					'fe_inserticon'	=>	$mmforum['new']['fe_inserticon']
				);
				$res=$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					'tx_mmforum_syntaxhl',
					$insertArr
				);
			}
			else {
				$UpdateArr = array(
					'lang_title' 	=>	$mmforum['lang_title'][$key],
					'lang_pattern' 	=>	$mmforum['lang_pattern'][$key],
					'lang_code'		=>	$mmforum['lang_code'][$key],
					'fe_inserticon'	=>	$mmforum['fe_inserticon'][$key]
				);
				$res=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_mmforum_syntaxhl','uid='.$key,$UpdateArr);
			}
		}

		// Display syntaxhl editing form
		$path=t3lib_extMgm::extPath('mm_forum').'/res/img/default/editor_icons/';
		$files=t3lib_div::getFilesInDir($path,'gif');
		$firstFile='';
		if(count($files)>0) {
			foreach($files as $k=>$f) {
				if($firstFile=='')$firstFile=$f; 
				$surlOptions.='<option value="'.$f.'"'.($mmforum['new']['fe_inserticon']==$f?'selected="selected"':'').'>'.$f.'</option>'; 
			}
		}

		$i=0;
		if(!isset($mmforum['new']['fe_inserticon']))  $mmforum['new']['fe_inserticon']=$firstFile;
		
		
		$content .= '<table cellpadding="2" cellspacing="0" class="mm_forum-list" width="100%">';
		$content .= '<tr>
						<td class="mm_forum-listrow_header">&nbsp;</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.fe_inserticon').'</td>
						<td class="mm_forum-listrow_header">&nbsp;</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.lang_title').'</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.lang_pattern').'</td>
						<td class="mm_forum-listrow_header">'.$LANG->getLL('tools.lang_code').'</td>
						<td class="mm_forum-listrow_header">&nbsp;</td></tr>';
		$content .= '<tr>
						<td class="mm_forum-listrow"><b>['.$LANG->getLL('tools.new').']</b></td>
						<td class="mm_forum-listrow"><img src="../res/img/default/editor_icons/'.htmlspecialchars($mmforum['new']['fe_inserticon']).'" id="fe_inserticon_preview" /></td>
						<td class="mm_forum-listrow"><select name="mmforum[new][fe_inserticon]" onchange="document.getElementById(\'fe_inserticon_preview\').src=\'../res/img/default/editor_icons/\'+this[this.selectedIndex].value">'.$surlOptions.'</select></td>
						<td class="mm_forum-listrow"><input type="text" name="mmforum[new][lang_title]" value="'.htmlspecialchars($mmforum['new']['lang_title']).'" /></td>
						<td class="mm_forum-listrow"><input type="text" name="mmforum[new][lang_pattern]" value="'.htmlspecialchars($mmforum['new']['lang_pattern']).'" /></td>
						<td class="mm_forum-listrow"><select name="mmforum[new][lang_code]">'.$this->getFileOptionFields('../includes/geshi/geshi/','php','',FALSE).'</select></td>
						<td align="right" class="mm_forum-listrow"><input type="image" src="../../../../typo3/sysext/t3skin/icons/gfx/savedok.gif" name="mmforum[save][0]" /></td>
					</tr>';
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_mmforum_syntaxhl','deleted=0','','uid asc');

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= (!$row['hidden']) ? '<tr class="mm_forum-listrow'.($i++ % 2==0 ? '' : '2').'">' : '<tr class="hidden">';
			$content .= '	<td>&nbsp;</td>';
			$content .= '	<td><img src="../res/img/default/editor_icons/'.$row['fe_inserticon'].'" /></td>';
			$content .= '	<td><select name="mmforum[fe_inserticon]['.$row['uid'].']">'.$this->getFileOptionFields('../res/img/default/editor_icons/','',$row[fe_inserticon],TRUE).'</select></td>';
			$content .= '	<td><input type="text" name="mmforum[lang_title]['.$row['uid'].']" value="'.$row['lang_title'].'" /></td>';
			$content .= '	<td><input type="text" name="mmforum[lang_pattern]['.$row['uid'].']" value="'.$row['lang_pattern'].'" /></td>';
			$content .= '	<td><select name="mmforum[lang_code]['.$row['uid'].']">'.$this->getFileOptionFields('../includes/geshi/geshi/','php',$row[lang_code],FALSE).'</select></td>';       
			$content .= '	<td align="right">';
			$content .= '		<a href="index.php?mmforum[tools]=3&mmforum[delete]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/garbage.gif" /></a>&nbsp;&nbsp;';
			$content .= 		$row['hidden']?'<a href="index.php?mmforum[tools]=3&mmforum[unhide]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/button_unhide.gif" /></a>&nbsp;&nbsp;':'<a href="index.php?mmforum[tools]=3&mmforum[hide]['.$row['uid'].']=1"><img src="../../../../typo3/sysext/t3skin/icons/gfx/button_hide.gif" /></a>&nbsp;&nbsp;';
			$content .= '		<input style="border:0px;" type="image" src="../../../../typo3/sysext/t3skin/icons/gfx/savedok.gif" name="mmforum[save]['.$row['uid'].']" />';
			$content .= '	</td>';
			$content .= '</tr>';
		}
		$content.='</table>';
		return $content;
	}
    
    /**
     * Read out all files by given path, fileExt, and OpVar (Operator) for getting builded Options (SELECT BOX Options)
     * @author   Björn Detert <b.detert@mittwald.de>
     * @version  2007-05-04
     * @return   string 
     */
    function getFileOptionFields($path,$fileExt,$opVar = '',$noDel){
		$files=t3lib_div::getFilesInDir($path,$fileExt);
		$Options = '';
		if(count($files)>0) {
			foreach($files as $k=>$f) {
				$name = ($noDel === FALSE)?  str_replace('.'.$fileExt,'',$f): $f;
				$Options.='<option value="'.$name.'"'.($opVar==$name?'selected="selected"':'').'>'.$name.'</option>'; 
			}
		}
		return $Options;
	}
    
    /**
     * Displays the data import form. See mod1/class.tx_mmforum_import.php for more information.
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2007-05-02
     * @return  string The data import content
     */
    function Import() {
        $import = t3lib_div::makeInstance('tx_mmforum_import');
        $import->p = $this;
        return $import->main('');
    }
    
    /**
     * Displays the mm_forum install module. See mod1/class.tx_mmforum_install.php for more information.
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2007-05-14
     * @return  string The install module content.
     */
    function Install() {
        $install = t3lib_div::makeInstance('tx_mmforum_install');
        $install->p = $this;
        return $install->main('');
    }
    
    /**
     * Displays the fe_user table extension module. See mod1/class.tx_mmforum_userfields.php for more information.
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2007-05-15
     * @return  string The fe_user table extension module.
     */
    function UserFields() {
        $userFields = t3lib_div::makeInstance('tx_mmforum_userFields');
        $userFields->p = $this;
        return $userFields->main('');
    }
    
    /**
     * Displays the statistic module. See mod1/class.tx_mmforum_statistics.php for more information.
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2007-05-29
     * @return  string The fe_user table extension module.
     */
    function Statistics() {
        $stats = t3lib_div::makeInstance('tx_mmforum_statistics');
        $stats->p = $this;
        return $stats->main('');
    }
    
    /**
     * Displays the user ranks module. See mod1/class.tx_mmforum_ranksbe.php for more information.
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @return  string The user ranks module
     */
    function Ranks() {
        $ranks = t3lib_div::makeInstance('tx_mmforum_ranksBE');
        $ranks->p = $this;
        return $ranks->main('');
    }
    
    /**
     * Displays the updater module. See mod1/class.tx_mmforum_updater.php for more information.
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2008-04-29
     * @return  string The updater module
     */
    function Updater() {
    	$updater = t3lib_div::makeInstance('tx_mmforum_updater');
    	$updater->p = $this;
    	return $updater->main();
    }
    
    /**
     * Miscellaneous helper functions
     */
    
    /**
     * Generates a parameter string for links that are to be used in
     * this module.
     * @param  array  $arr An associative array of whose elements the parameter
     *                     string is created. This string is created using the pattern
     *                     mmforum[key]=value.
     * @return string      The parameter string ready to be appended to an URL.
     */
    function linkParams($arr) {
        foreach($arr as $key=>$val) {
            $l.="mmforum[$key]=$val&";
        }
        return substr($l,0,strlen($l)-1);
    } 

    /**
     * Determines the amount of all boards used in the extension, grouped by
     * parent category.
     * @return array An array containing information on the board amount of each
     *               category. Follows the pattern [Category UID]=>[Board Count]
     */
    function getForenCount() {
        $forumCount=array();
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('parentID,count(*) as total','tx_mmforum_forums','deleted=0 and parentID>0','parentID','parentID asc');    
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $forumCount[$row['parentID']]=$row['total'];
        }
        return $forumCount;
    }

    /**
     * Reads all user groups into an array.
     * @return array An array containing information on all feuser groups. The
     *               array follows the pattern [Group UID]=>[Group name]
     */
    function feGroups2Array() {
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','fe_groups','hidden=0 and deleted=0','','uid asc');
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {  
            $ug[$row['uid']]=$row['title'];
        }
        return $ug;
    }

    /**
     * Generates an icon leading to a record editing page using TYPO3-internal
     * functions. Used for example to provide icons allowing to edit fe_user
     * records.
     * @param  string $table The table from which a record is to be edited.
     * @param  array  $row   The record to be edited as associative array.
     * @return string        An icon linked to the editing form
     */
    function getItemFromRecord($table,$row) {
		global $BACK_PATH,$LANG,$TCA,$BE_USER;

		$iconAltText	= t3lib_BEfunc::getRecordIconAltText($row,$table);
		$elementTitle	= t3lib_BEfunc::getRecordPath($row['uid'],'1=1',0);
		$elementTitle	= t3lib_div::fixed_lgd_cs($elementTitle,-($BE_USER->uc['titleLen']));
		$elementIcon	= t3lib_iconworks::getIconImage($table,$row,$BACK_PATH,'class="c-recicon" title="'.$iconAltText.'"');
		
		$params = '&edit['.$table.']['.$row['uid'].']=edit';
		$editOnClick = t3lib_BEfunc::editOnClick($params,$BACK_PATH);
		
		return '<a href="#" onclick="'.htmlspecialchars($editOnClick).'">'.$elementIcon.'</a>';
	}
	
	/**
	 * Converts a commaseperated list of record UIDs to a TCEforms-readable format.
	 * This function converts a regular list of commaseperated record UIDs (like e.g.
	 * "1,2,3") to a format that can be interpreted as form input field default value
	 * by the t3lib_TCEforms class (like e.g. "1|Username,2|Username_two,3|Username_three").
	 * 
	 * @param   string $list      The commaseperated list
	 * @param   string $table     The table, the records' titles are to be loaded from
	 * @param   string $fieldname The fieldname used to identify the records, like for example
	 *                            the username in the fe_users table.
	 * 
	 * @author  Martin Helmich <m.helmich@mittwald.de>
	 * @version 2007-04-23
	 */
	function convertToTCEList($list,$table,$fieldname) {
		$items = t3lib_div::trimExplode(',',$list);
		if(count($items)==0) return '';
		
		foreach($items as $item) {
			if($item=='') continue;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fieldname,$table,'uid="'.$item.'"');
			list($title) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			$resultItems[] = "$item|$title";
		}
		if(count($resultItems)==0) return '';
		return implode(',',$resultItems);
	}
    
    /**
     * Configuration variable management
     */
    
    /**
     * Loads the module configuration vars. The backend module stores the
     * configuration parameters made by the user in an external typoscript file
     * (tx_mmforum_config.ts) that is included into the global typoscript setup.
     * 
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2007-05-07
     * @return  void
     */
    function loadConfVars() {
        $conf    = file_get_contents('../ext_typoscript_constants.txt');
        if(file_exists($this->configFile))
            $conf   .= file_get_contents($this->configFile);
            
        $parser  = t3lib_div::makeInstance('t3lib_TSparser');
        $parser->parse($conf);
        
        $this->config = $parser->setup;
        
        $this->confArr['templatePath']      = $this->config['plugin.']['tx_mmforum.']['path_template'];
        $this->confArr['userPID']           = $this->config['plugin.']['tx_mmforum.']['userPID'];
        $this->confArr['forumPID']          = $this->config['plugin.']['tx_mmforum.']['storagePID'];
        $this->confArr['userGroup']         = $this->config['plugin.']['tx_mmforum.']['userGroup'];
        $this->confArr['modGroup']          = $this->config['plugin.']['tx_mmforum.']['moderatorGroup'];
        $this->confArr['adminGroup']        = $this->config['plugin.']['tx_mmforum.']['adminGroup'];
        $this->confArr['recordsPerPage']    = 25;
    }
    
    /**
     * Updates a configuration variable. The change is written to the
     * external typoscript file.
     * 
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2007-05-07
     * @param   string $elem  The element whose value is to be set.
     * @param   string $value The new element value.
     * @return  void
     */
    function setConfVar($elem,$value) {
        if($this->config['plugin.']['tx_mmforum.'][$elem]!=$value) {
            $this->config['plugin.']['tx_mmforum.'][$elem] = $value;
            
            $confFile = fopen($this->configFile,'w');
            fwrite($confFile,"# Last updated ".date("Y-m-d H:i")." by mm_forum backend module.\r\n");
            fwrite($confFile,$this->parseConf());
            fclose($confFile);
        }
    }
    
    /**
     * Parses the configuration variables to prepare them for being
     * written to the configuration file.
     * 
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2007-05-07
     * @param   array  $conf The configuration array to be parsed. Can be left
     *                       empty in order to parse the entire configuration array.
     *                       This parameter is only needed because this function
     *                       is called recursively.
     * @param   int    $ind  The line indent in tabulator characters before a new element.
     *                       This parameter is increased with increasing recursion depth
     *                       in order to create a nice code. This is not needed, since probably
     *                       nobody will take a look at the tx_mmforum_config.ts anyway.
     * @return  string       The configuration variables encoded in TypoScript.
     */
    function parseConf($conf=FALSE,$ind=0) {
        if($conf === FALSE) $conf = $this->config;
        
        foreach($conf as $k => $v) {
            $result .= $this->getInd($ind);
            if(is_array($v)) {
                $k = substr($k,0,strlen($k)-1);
                $result .= $k.' {'."\r\n".$this->parseConf($v,$ind+1).$this->getInd($ind)."}\r\n";
            }
            else $result .= $k.' = '.$v."\r\n";
        }
        
        return $result;
    }
    
    /**
     * Determines if the mm_forum extension is properly configured.
     * 
     * @author  Martin Helmich <m.helmich@mittwald.de>
     * @version 2007-05-14
     * @return  boolean TRUE, if the extension is properly configured, otherwise FALSE.
     */
    function getIsConfigured() {
        if(intval($this->confArr['userPID'])==0 || intval($this->confArr['forumPID'])==0 ||
           intval($this->confArr['userGroup'])==0 || intval($this->confArr['adminGroup'])==0 )
           return false;
        return true;
    }
    
    /**
     * Generates a line indent for the configuration array output.
     * @param  int    $ind The amount of tab characters to be created.
     * @return string      A list of $ind tab characters.
     */
    function getInd($ind) {
        for($i=0;$i<$ind;$i++) $result .= "\t"; return $result;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mm_forum/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mm_forum/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_mmforum_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>