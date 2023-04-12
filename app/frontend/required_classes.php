<?php
/*
	Lista de clases para cargar automágicamente en el framework.
	El orden importa!
*/
$loading_classes = array(
	DIR_includes . "constants.inc.php",
	DIR_includes . "class.logging.inc.php",
	DIR_model . "class.contenidos.inc.php",
	// DIR_model ."usuarios" . DS . "class.usuarios_backend.inc.php", // NO es necesario en el front.
	// DIR_model ."wsusuarios" . DS . "class.usuarios_backend.inc.php", // Alternativa por web service
	DIR_includes . "languages.inc.php",
	DIR_includes . "class.sidekick.inc.php"
);

if (isset($required_classes)) {	$required_classes = array_merge($required_classes, $loading_classes);} else { $required_classes = $loading_classes; } unset($loading_classes);
?>