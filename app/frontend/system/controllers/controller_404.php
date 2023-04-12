<?php
/*
	Este es el controlador para el error 404.
	Created: 2021-02-06
	Author: DriverOp
*/
header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');

$contenidos = ParseMetadata($objeto_contenido->metadata, $objeto_contenido->alias);
$_collectMsgs = null;
/*
	La vista del error 404 depende de si el framework está en modo backend y si lo está, no es lo mismo que el usuario esté o no esté logeado.
*/
$contenidos['template'] = CustPath(DIR_plantillas.'404_inside.htm');

if (INTERFACE_TYPE == 'backend') {
	if (!$user_logged_in) {
		$contenidos['template'] = CustPath(DIR_plantillas.'404_outside.htm');
		$contenidos['hasmainmenu'] = false;
		$contenidos['header'] = CustPath(DIR_common.'header_simple.htm');
		$contenidos['footer'] = CustPath(DIR_common.'footer_simple.htm');
	}
}

try {
	/* Template */
	if (!ExisteArchivo($contenidos['template'])) {
		$_collectMsgs .= 'Plantilla no encontrada: '.$contenidos['template'].PHP_EOL;
		throw new Exception('Can\'t continue without a template, sorry...');
	}

	/* Head */
	if (!ExisteArchivo($contenidos['head'])) { $_collectMsgs .= 'Head no encontrado: '.$contenidos['head'].PHP_EOL; $contenidos['head'] = null; }

	/* Header */
	if (!ExisteArchivo($contenidos['header'])) { $_collectMsgs .= 'Header no encontrado: '.$contenidos['header'].PHP_EOL; $contenidos['header'] = null; }
	
	/* Main menu */
	if ($contenidos['hasmainmenu']) {
		if (!ExisteArchivo($contenidos['mainmenu'])) { $_collectMsgs .= 'Mainmenu no encontrado: '.$contenidos['mainmenu'].PHP_EOL; $contenidos['mainmenu'] = null; }
	}

	/* Submenu */
	if ($contenidos['hassubmenu']) {
		if (!ExisteArchivo($contenidos['submenu'])) { $_collectMsgs .= 'Submenu no encontrado: '.$contenidos['submenu'].PHP_EOL; $contenidos['submenu'] = null; }
	}
	/* Vista */
	if (!ExisteArchivo($contenidos['vista'])) { $_collectMsgs .= 'Vista no encontrado: '.$contenidos['vista'].PHP_EOL; $contenidos['vista'] = null; }

	/* Footer */
	if (!ExisteArchivo($contenidos['footer'])) { $_collectMsgs .= 'Footer no encontrado: '.$contenidos['footer'].PHP_EOL; $contenidos['footer'] = null; }
	
	include($contenidos['template']);

	if ($_collectMsgs) {
		cLogging::Write(__FILE__.' '.__LINE__.' '.$_collectMsgs,LGEV_WARN);
		if (DEVELOPE) { WarnLogP($_collectMsgs); }
	}
} catch(Exception $e) {
	cLogging::Write(__FILE__.' '.__LINE__.' '.$e->getMessage(), LGEV_ERROR);
	if (DEVELOPE) { WarnLogP($e->getMessage()); }
}
