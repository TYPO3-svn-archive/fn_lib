<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Frank Nägler <typo3@naegler.net>
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

define(LOGLEVEL_INFO, 1);
define(LOGLEVEL_NOTICE, 2);
define(LOGLEVEL_WARNING, 4);
define(LOGLEVEL_ERROR, 8);
define(LOGLEVEL_FATAL, 16);
define(LOGLEVELS, array(
	0,
	LOGLEVEL_INFO,
	LOGLEVEL_NOTICE,
	LOGLEVEL_WARNING,
	LOGLEVEL_ERROR,
	LOGLEVEL_FATAL
));

/**
 * Class 'tx_fnlib_base' for the 'fn_lib' extension.
 *
 * @author	Frank Nägler <typo3@naegler.net>
 * @package	TYPO3
 * @subpackage	fn_lib
 */
class tx_fnlib_base {
	var $classes = array(
		'tx_fnlib_twitter'		=>	'lib/class.tx_fnlib_twitter.php',
		'tx_fnlib_twitpic'		=>	'lib/class.tx_fnlib_twitpic.php',
		'tx_fnlib_hookHandler'	=>	'lib/class.tx_fnlib_hookHandler.php'
	);

	/**
	 * contructur methode for this class
	 *
	 * @return tx_fnlib_base
	 */
	function tx_fnlib_base() {
		$this->init();
	}

	/**
	 * this methode make some inititial things like set global vars
	 * or include all needed classes.
	 *
	 */
	function init() {
		$this->EXT_PATH = t3lib_extMgm::extPath('fn_lib');
		foreach ($this->classes as $k => $v) {
			t3lib_div::requireOnce($this->EXT_PATH . $this->classes[$k]);
		}
	}
	
	/**
	 * set an logging level
	 *
	 * @param string $level
	 * @return void
	 */
	function setLogLevel($level=0) {
		if (in_array($level, LOGLEVELS)) {
			$this->logLevel = $level;
		} else {
			$this->logLevel = 0;
		}
	}
	
	/**
	 * send sysLog request if no loglevel is set or if a global loglevel is set
	 * than only if the given loglevel $level is lower as the global loglevel
	 *
	 * @param mixed $msg string or array which should be logged
	 * @param string $extKey
	 * @param string $level [fatal, error, warning, notive, info]
	 */
	function log($msg, $extKey, $level = '') {
		if (in_array($level, LOGLEVELS)) {
			$errLevel = $level;
		} else {
			$errLevel = 0;
		}
		
		if (is_array($msg)) {
			$msg = t3lib_div::arrayToLogString($msg);
		}
		
		if (isset($this->logLevel) && ($errlevel < $this->logLevel)) {
			return;
		}
		t3lib_div::sysLog($msg, $extKey, $errlevel);
	}
	
	/**
	 * get config settings from flexform
	 *
	 * @param object $pObj
	 * @return array $conf
	 */
	function getFlexformConf(&$pObj) {
		$piFlexForm = $pObj->cObj->data['pi_flexform'];
		foreach ( $piFlexForm['data'] as $sheet => $data ) {
			foreach ( $data as $lang => $value ) {
				foreach ( $value as $key => $val ) {
					$conf[$key] = $pObj->pi_getFFvalue($piFlexForm, $key, $sheet);
				}
			}
		}
		return $conf;
	}
	
	/**
	 * this methode get back an instance of the
	 * twitter client
	 *
	 * @return object	tx_fnlib_twitter instance
	 */
	function getTwitterClient($user='', $pass='') {
		return $this->twitterClient = new tx_fnlib_twitter($user, $pass);
	}

	/**
	 * this methode get back an instance of the
	 * twitter client
	 *
	 * @return object	tx_fnlib_twitter instance
	 */
	function getTwitpicClient($user, $pass) {
		return $this->twitpicClient = new tx_fnlib_twitpic($user, $pass);
	}

	/**
	 * this methode get back an instance of the
	 * hookHandler class
	 *
	 * @return object	tx_fnlib_hookHandler instance
	 */
	function getHookHandler($extKey) {
		// return $this->hookHandlert = t3lib_div::makeInstance($this->classes['tx_fnlib_hookHandler']);
		return $this->hookHandlert = new tx_fnlib_hookHandler($extKey);
	}
	
	/**
	 * Gets all "lang_ and label_" Marker for substition with substituteMarkerArray
	 * Take function from tx_commerce
	 * @return      void
	 * @since 10.02.06 Changed to XML
	 * @coauthor Frank Kroeber <fk@marketing-factory.de>
	 * @coauthor Frank Nägler <typo3@naegler.net>
	 */
	function generateLanguageMarker(&$pObj) {
		$pObj->languageMarker = array();
		if (
			(is_array($pObj->LOCAL_LANG[$GLOBALS['TSFE']->tmpl->setup['config.']['language']]))
			&&
			(is_array($pObj->LOCAL_LANG['default']))
		) {
			$markerArr = array_merge($pObj->LOCAL_LANG['default'],
			$pObj->LOCAL_LANG[$GLOBALS['TSFE']->tmpl->setup['config.']['language']]);
		} elseif (is_array($pObj->LOCAL_LANG['default'])) {
			$markerArr=$pObj->LOCAL_LANG['default'];
		} else {
			$markerArr=$pObj->LOCAL_LANG[$GLOBALS['TSFE']->tmpl->setup['config.']['language']];
		}
		
		while(list($k,$v)=each($markerArr)) {
			if (stristr($k,'lang_') OR stristr($k,'label_')) {
				$pObj->languageMarker['###'.strtoupper($k).'###'] = $pObj->pi_getLL($k);
			}
		}
	}
	
	/**
	 * generates a markerArray by given table and uid or where clause
	 *
	 * @param string 	$table
	 * @param int		$uid
	 * @param string	$where
	 * @return array
	 */
	function generateTableMarker($table, $uid = 0, $where = '') {
		$tableMarker = array();
		if ($uid == 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$table,
				$where
			);
		} else {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$table,
				'uid = ' . $uid . $where
			);
		}
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 1) {
			$data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			foreach ($data as $k => $v) {
				$tableMarker['###' . strtoupper($table) . '_' . strtoupper($k) . '###'] = $data[$k];
			}
		} else if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 1) {
			while ($data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$tempMarker = null;
				foreach ($data as $k => $v) {
					$tempMarker['###' . strtoupper($table) . '_' . strtoupper($k) . '###'] = $data[$k];
				}
				$tableMarker[] = $tempMarker;
			}
		}
		return $tableMarker;
	}
	
	/**
	 * this methode parse the given markerArray into the subpart
	 * of the given template and return the parsed template code.
	 *
	 * @param array		$conf
	 * @param string	$subpart
	 * @param array		$markerArray
	 * @param object	$pObj
	 * @return string
	 */
	function parseTemplate(&$conf, $subpart, &$markerArray, &$pObj) {
		$template = $pObj->cObj->fileResource($conf['template']);
		$templateCode = $pObj->cObj->getSubpart($template, '###TEMPLATE_'.strtoupper($subpart).'###');
		return $pObj->cObj->substituteMarkerArray($templateCode, $markerArray);
	}
	
	/**
	 * get TypoScript config array by given key
	 *
	 * @param string $key
	 * @return array
	 */
	function getTypoScript4PlugIn($key) {
		return $GLOBALS['TSFE']->tmpl->setup['plugin.'][$key.'.'];
	}

}

?>
