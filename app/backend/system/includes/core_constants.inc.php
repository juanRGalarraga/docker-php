<?php
/*
	Lista de constantes útiles al desarrollo.
	
*/
const ESTADOS_ADELANTOS = array(
	'INIC'=>'Iniciado no trasferida', // El adelanto fu creado pero no se ha realizaco la transferencia todavía.
	'PEND'=>'Pendiente de cobro', // El adelanto fue transferido y está pendiente de cobro.
	'HOLD'=>'En pausa', // El adelanto está pausado, es válido pero no se puede operar sobre él.
	'CANC'=>'Cobro efectuado', // El adelanto fue cobrado.
	'MORA'=>'Vencido', // El adelanto pasó la fecha de vencimiento sin ser cobrado.
	'ANUL'=>'Anulado' // El adelanto está anulado.
);

const ESTADOS_SOLICITUDES = array(
	'INIC'=> "Iniciada",
	'PEND'=>'Pendiente',
	'APRO'=>"Aprobada",
	'RECH'=>'Rechazada',
	'FAIL'=>'Fallo interno',
	'ANUL'=>'Anulada'
);

const ESTADOS_PRESTAMOS = array(
	'SOLIC' => "Solicitado",
	'PEND' => "Pendiente",
	'CANC' => "Pagado",
	'MORA' => "En mora",
	'REFIN' => "Refinanciado",
	'HOLD' => "Cobro pendiente",
	'ANUL' => "Anulado",
	'HDAL' => "Pendiente de anulación"
);



const ESTADOS_COLORES = array(
	'SOLIC' => "#2FDCFF",
	'PEND' => "#33FFAC",
	'CANC' => "#28A745",
	'MORA' => "#DC3545",
	'REFIN' => "#6C757D",
	'HOLD' => "#FD7E14 ",
	'ANUL' => "#6C757D",
	'HDAL' => "#909396",
	'ACRE' => "#28A745",
	'RECH' => "#DC3545"
);

const ESTADOS_COLORES_SOLICITUDES = array(
	'APRO' => "#28A745",
	'PEND' => "#33FFAC",
	'CANC' => "#28A745",
	'RECH' => "#DC3545",
	'FAIL' => "#DC3545",
);



const ESTADOS_COBROS = array(
	"PEND"=>"Pendiente",
	"ACRE"=>"Acreditado",
	"RECH"=>"Rechazado",
	"ANUL"=>"Anulado",
);

const TIPOS_ENTEROS = ['int','bigint','tinyint'];
const TIPOS_FLOTANTES = ['float','decimal'];
const TIPOS_FECHAS = ['date','datetime'];
const TIPOS_ENUM = ['enum','set'];

const VALID_TRUE_VALUES = array('true','verdadero','si','sí','yes','1','hab','enabled','1'); // Cadenas que evaluan como true.
const VALID_FALSE_VALUES = array('false','falso','no','not','des','disabled','0','undefined','null'); // Cadenas que evaluan como false.
const VALID_TYPES_VALUES = array('STRING','INT','FLOAT','BOOL','MONEDA','JSON'); // Los tipos de datos válidos almacenados en la tabla.

const VALID_TYPE_PERMISOS = array(
	'ADMIN'=>"Administrador",
	'OPER'=>"Operador",
	'OWNER' => 'Propietario'
);

define('ESTADOS_VALIDOS', ['HAB'=>'Habilitado', 'DES'=>'Deshabilitado', 'ELI'=>'Eliminado']);

const ESTADOS_VALIDOS_COLORES = array(
	'HAB'=>'<span class="font-weight-bold">%s</span>',
	'DES'=>'<span class="font-weight-bold text-warning">%s</span>',
	'ELI'=>'<span class="font-weight-bold text-danger">%s</span>'
);
const OPERACIONES_VALIDAS = array(
	'ADD'=>'Sumar',
	'SUB'=>'Restar',
	'NUL'=>'Neutral'
);

const JSON_DOMICILIO = '{
	"calle":"%s",
	"nro_externo":"%s",
	"nro_interno":"%s",
	"colonia":"%s"
}';

const JSON_TELEFONO = '{
	"cod_area":"%s",
	"num_abonado":"%s",
	"comp_movil":"%s",
	"uso":"%s"
}';

const UPLOAD_ERROR_MSG = array(
	        UPLOAD_ERR_OK=>"No hay error",
      UPLOAD_ERR_INI_SIZE=>"El archivo es más grande que lo permitido por el Servidor.",
	 UPLOAD_ERR_FORM_SIZE=>"El archivo subido es demasiado grande.",
	   UPLOAD_ERR_PARTIAL=>"El archivo subido no se terminó de cargar (probablemente cancelado por el usuario).",
	   UPLOAD_ERR_NO_FILE=>"No se subi&oacute; ningún archivo",
	UPLOAD_ERR_NO_TMP_DIR=>"Error del servidor: Falta el directorio temporal.",
	UPLOAD_ERR_CANT_WRITE=>"Error del servidor: Error de escritura en disco",
	 UPLOAD_ERR_EXTENSION=>"Error del servidor: Subida detenida por la extensión"
);

const PRIORIDADES_VALIDAS = array('NUEVA','BAJA','NORMAL','ALTA','URGENTE','INMEDIATA');
const ESTADOS_INCIDENCIAS_VALIDOS = array('NUEVA','MASINF','ACEPTADA','CONFIRMADA','ASIGNADA','RESUELTA','CERRADA');

const EXTENSIONES_IMAGENES = array('png','jpg','jpeg','webp');
const JSON_ERROR_MSG_ESP = array(
				 JSON_ERROR_DEPTH => 'Se excedió la profundidad máxima de la pila.',
		JSON_ERROR_STATE_MISMATCH => 'Subdesbordamiento o el modo no coincide.',
			 JSON_ERROR_CTRL_CHAR => 'Se encontró un caracter de control inesperado.',
				JSON_ERROR_SYNTAX => 'Error de sintaxis. JSON mal formado.',
				  JSON_ERROR_UTF8 => 'Caracter UTF-8 mal formado, posiblemente mal codificado.',
			 JSON_ERROR_RECURSION => 'Una o más referencias recursivas en el valor a codificar',
			JSON_ERROR_INF_OR_NAN => 'Uno o más valores NAN o INF en el valor a codificar',
	  JSON_ERROR_UNSUPPORTED_TYPE => 'Se proporcionó un valor de un tipo que no se puede codificar',
 JSON_ERROR_INVALID_PROPERTY_NAME => 'Se dio un nombre de una propiedad que no puede ser codificada',
				 JSON_ERROR_UTF16 => 'Caracteres UTF-16 malformados, posiblemente codificados de forma incorrecta'
);
const EVENTS_TYPES = array('ALL','DEBUG','INFO','WARN','ERROR','FATAL','OFF','TRACE');

const ESTADO_INCIDENCIAS = array('NUEVA','MASINF','ACEPTADA','CONFIRMADA','ASIGNADA','RESUELTA','CERRADA');
const PRIORIDAD_INCIDENCIAS = array('NUEVA','BAJA','NORMAL','ALTA','URGENTE','INMEDIATA');

//Lista de colores de la consola
const CLI_COLORES = array(
	"Negro" => "\e[0;30m",
	"Gris oscuro" => "\e[1;30m",
	"Rojo" => "\e[0;31m",
	"Rojo claro" => "\e[1;31m",
	"Verde" => "\e[0;32m",
	"Verde claro" => "\e[1;32m",
	"Marron" => "\e[0;33m",
	"Amarillo" => "\e[1;33m",
	"Azul" => "\e[0;34m",
	"Azul claro" => "\e[1;34m",
	"Magenta" => "\e[0;35m",
	"Magenta claro" => "\e[1;35m",
	"Cian" => "\e[0;36m",
	"Cian claro" => "\e[1;36m",
	"Gris claro" => "\e[0;37m",
	"Blanco" => "\e[1;37m",
	"Ninguno" => "\e[0m"
);

// Caracteres para reemplazo.
const reptildes=array(
	'à','á','â','ã','ä','å','æ',
	'À','Á','Â','Ã','Ä','Æ','Å',
	'è','é','ê','ë','œ',
	'È','É','Ê','Ë','Œ',
	'ï','ì','¡','¡','î','í',
	'Í','Ì','Ï','Î',
	'ò','ô','ó','ö','õ','ð','ø',
	'Ò','Ó','Ô','Ö','Õ','Ø',
	'ù','ú','ü','û',
	'Ù','Ú','Û','Ü',
	'ý','ÿ','Ÿ','Ý','ç','Ç','Ð','ß','ñ','Ñ','°','_','~','!','¡','&','$','#','*','`','´'
);
const repplanas=array('a','a','a','a','a','a','a','A','A','A','A','A','A','A','e','e','e','e','e','E','E','E','E','E','i','i','i','i','i','i','I','I','I','I','o','o','o','o','o','o','o','O','O','O','O','O','O','u','u','u','u','U','U','U','U','y','y','Y','Y','c','C','D','b','n','N',' ',' ','','','','y','s','','',"'","'");


const DOCUMENTOS = array(
	"DNI" => "DNI",
	"CUIT" => "CUIT",
	"LC" => "Libreta Cívica",
	"CI" => "Carnet Iidentificación",
	"LE" => "Libreta Enrolamiento",
	"PAS" => "Pasaporte"
);

const VALOR_APLICAR = array("CAPITALMAS"=>"Capital Mas","CAPITALMENOS"=>"Capital Menos","INTERES"=>"Interes","CAPITALMASINTERES"=>"Capital Mas Interes","TODO"=>"Todos");

const CALCULOS = array("PORC"=>"Porcentaje","FIJO"=>"Cargo fijo","TASA"=>"Tasa");

