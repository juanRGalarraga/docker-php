<?php
/*
	Clase de exceptiones del acceso a la base de datos. Se usa en la case cDb (dbutil)
	Created: 2021-08-14
	Author: Rebrit SRL.
*/

require_once(DIR_includes."class.logging.inc.php");

class DBException extends Exception {
	
	public function __construct($message, $codenumber = 0, Exception $previous = null) {
		parent::__construct($message, $codenumber, $previous);
		$error_level = LGEV_WARN;
		$trace = debug_backtrace();
		$line = sprintf('%s:%s %s%s', $this->getFile(), $this->getLine(), ((isset($trace[1]['class']))?$trace[1]['class'].'->':null), @$trace[1]['function']);
		$line .= ' '.$message;
		if (isset($trace[1]) and !empty($trace[1]['class']) and ($trace[1]['class'] == 'cDb') and !empty($trace[1]['object']->lastsql)) {
			$error_level = LGEV_ERROR;
			$line .= ' SQL: '.$trace[1]['object']->lastsql;
		}
		cLogging::LogToFile($line, '_db', $error_level);
	}
	
}
