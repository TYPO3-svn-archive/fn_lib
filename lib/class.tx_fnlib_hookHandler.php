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


/**
 * Class 'tx_fnlib_hookHandler' for the 'fn_lib' extension.
 *
 * @author	Frank Nägler <typo3@naegler.net>
 * @package	TYPO3
 * @subpackage	fn_lib
 */
class tx_fnlib_hookHandler {
	
	/**
	 * contructur methode for this class
	 *
	 * @return tx_fnlib_hookHandler
	 */
	function tx_fnlib_hookHandler($extKey) {
		$this->extKey = $extKey;
		$this->init();
	}
	
	/**
	 * this methode make some inititial things like set global vars
	 * or include all needed classes.
	 *
	 */
	function init() {
		$this->hookObjectsArr = array();
		// init Hooks for given Extension
		if (is_array ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['hookHandler'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['hookHandler'] as $classRef) {
				$this->hookObjectsArr[] = &t3lib_div::getUserObj($classRef);
			}
		}
	}
	
	/**
	 * this method calls a method of a each registerd hookObj
	 *
	 * @param string $method
	 * @param mixed	some other params which will be transfered to the hook methods
	 * @return defined by called hook object method
	 */
	function call($method) {
		$params = func_get_args();
		array_shift($params);
		if(is_array($this->hookObjectsArr)){
			foreach($this->hookObjectsArr as $hookObj) {
				if (method_exists($hookObj, $method)) {
					return call_user_func_array(array(&$hookObj, $method), $params);
				}
			}
		}
	}
}
?>