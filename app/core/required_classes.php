<?php
/*
	Lista de clases para cargar automágicamente en el framework.
	El orden importa!
*/
$loading_classes = array(
	DIR_includes . "constants.inc.php",
	DIR_includes . "class.logging.inc.php", // Clase que facilita la escritura en los archivos de logs.
//	DIR_includes . "controlErrors.inc.php", // Establecer control de errores personalizados.
	DIR_model . "class.contenidos.inc.php", // Clase que maneja los contenidos del framework.
	DIR_includes . "class.sidekick.inc.php", // Clase con métodos estáticos, helper de sistema.
	DIR_model . "class.sysparams.inc.php" // Para recuperar valores de configuración desde el core.
);

if (isset($required_classes)) {	$required_classes = array_merge($required_classes, $loading_classes);} else { $required_classes = $loading_classes; } unset($loading_classes);
