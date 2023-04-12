<?php
/*
	Estas son las constantes con la configuración para la conexión al web service de Core Becas.
	Está acá pero debería estar en un directorio fuera de la rama del servidor web.
	Este archivo se usa en "class.wsv2Client.inc.php"
*/

define("WS_URL", "http://".$_ENV['DOCKER_CORE']."/v2/");// Dirección del Web Service V2 del Core Becas.
define("WS_USER", "mechawsuser"); 						// Usuario en el Web Service V2 de Core Becas.
define("WS_PASS", "mj5raft3sl2"); 						// Contraseña del usuario en el Web Service V2 de Core Becas.
define("WS_TOKEN_TTL", 3600);							// Tiempo de vida máximo del token en segundos.
define("WS_TIMEOUT", 30); 								// Tiempo de espera límite para las peticiones al WS, en segundos.
define("WS_LOG",DIR_logging); 							// Directorio raíz donde almacenar los logs de comunicación con el WS.
define("WS_LOG_PREFIX","wsv2"); 						// Prefijo de los archivos de logs para diferenciarlo del resto de los logs.
define("WS_DEBUG_LEVEL",2); 							// Nivel de detalle en los logs. 0: Nada excepto errores irrecupetables, 1: Datos enviados y respuestas cortas. 2: Todo.
define("WS_ECHO_LOG",false); 							// Además poner en pantalla los mensajes de logs.
define("WS_SESSION_NAME","MechaWSv2");					// Nombre de la sesión local donde almacenar los datos de la comunicación.
define("WS_RESPONSE_TYPE","object"); 					// Tipo de respuesta preferida. object: Objeto (valor por omisión), array: Array.
define("WS_ENCODE_CONTENT",false);						// Los mensajes al servidor serán cifrados con AES256CBC
define("WS_ENCODE_CONTENT_PASSWORD","[contraseña_secreta]");// La contraseña de cifrado.