<?php
/*
	Controlador para la pantalla de login.
	
	Created: ???
	Author: ???
	Modif: 2021-02-03
	Author: DriverOp
	Desc:
		Adaptación al framework 1.80
*/
$_SESSION["usuariodelminibackend"]["token"] = null;

$url = EnsureTrailingURISlash($objeto_contenido->GetLink(true));

$contenidos = ParseMetadata($objeto_contenido->metadata, $objeto_contenido->alias);
$_collectMsgs = null;

try {
	/* Template */
	if (!ExisteArchivo($contenidos['template'])) {
		$_collectMsgs .= ' Plantilla no encontrada: '.$contenidos['template'].PHP_EOL;
		WarnLogP('Can\'t continue without a template, sorry...');
		return;
	}

	/* Head */
	if (!ExisteArchivo($contenidos['head'])) { $_collectMsgs .= ' Head no encontrado: '.$contenidos['head'].PHP_EOL; $contenidos['head'] = null; }
	
	/* Main menu */
	if ($contenidos['hasmainmenu']) {
		if (!ExisteArchivo($contenidos['mainmenu'])) { $_collectMsgs .= ' Mainmenu no encontrado: '.$contenidos['mainmenu'].PHP_EOL; $contenidos['mainmenu'] = null; }
	}

	/* Submenu */
	if ($contenidos['hassubmenu']) {
		if (!ExisteArchivo($contenidos['submenu'])) { $_collectMsgs .= ' Submenu no encontrado: '.$contenidos['submenu'].PHP_EOL; $contenidos['submenu'] = null; }
	}
	/* Vista */
	if (!ExisteArchivo($contenidos['vista'])) { $_collectMsgs .= ' Vista no encontrada: '.$contenidos['vista'].PHP_EOL; $contenidos['vista'] = null; }

	/* Footer */
	if (!ExisteArchivo($contenidos['footer'])) { $_collectMsgs .= ' Footer no encontrado: '.$contenidos['footer'].PHP_EOL; $contenidos['footer'] = null; }
	
	include($contenidos['template']);

	if ($_collectMsgs) {
		cLogging::Write(__FILE__.' '.__LINE__.' '.$_collectMsgs,LGEV_WARN);
		if (DEVELOPE) { WarnLogP($_collectMsgs); }
	}
} catch(Exception $e) {
	cLogging::Write(__FILE__.' '.__LINE__.$e->getMessage(), LGEV_ERROR);
	if (DEVELOPE) { WarnLogP(__FILE__.' '.__LINE__.$e->getMessage()); }
}

?>