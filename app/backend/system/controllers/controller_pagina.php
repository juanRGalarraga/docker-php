<?php
/*
	Este es el controlador genérico para cualquier clase de contenido.
	Created: 2021-02-08
	Author: DriverOp
*/


$contenidos = ParseMetadata($objeto_contenido->metadata, $objeto_contenido->alias);
$_collectMsgs = null;
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

	// $objeto_usuario->GetPermisos();
	include($contenidos['template']);

	if ($_collectMsgs) {
		cLogging::Write(__FILE__.' '.__LINE__.' '.$_collectMsgs,LGEV_WARN);
		if (DEVELOPE) { WarnLogP($_collectMsgs); }
	}
} catch(Exception $e) {
	cLogging::Write(__FILE__.' '.__LINE__.$e->getMessage(), LGEV_ERROR);
	if (DEVELOPE) { WarnLogP(__FILE__.' '.__LINE__.$e->getMessage()); }
}
