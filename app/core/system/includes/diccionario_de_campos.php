<?php
/**
 * Diccionario de campos para no devolver los nombres directos al enviar una respuesta del core
 * Created: 2021-09-13
 * Author: Gastón Fernandez
 * Author Intelectual: El que lo creo para el core de tenela
 */
 
 //Campos que son normalmente devueltos
 const COMMON_FIELDS = array(
	"id" => "id",
    "nombre" => "Nombre",
    "apellido" => "Apellido",
	"sys_fecha_alta" => "FechaAlta",
    "sys_fecha_modif" => "FechaModif",
	"sys_usuario_id" => "UsuarioId"
 );
 
const FIELDS_LIST = array(
    "tipo_doc" => "TipoDoc",
    "nro_doc" => "NroDoc",
    "dni" => "NroDoc",
    "email" => "Email",
    "fecha_nac" => "FechaNacimiento",
    "fecha_nacimiento" => "FechaNacimiento",
    "tel_movil" => "TelefMovil",
    "dir" => "Direccion",
    "calle" => "Direccion",
    "direccion" => "Direccion",
    "ciudad_nombre" => "Ciudad",
    "localidad" => "Ciudad",
    "pais_nombre" => "Pais",
    "data" => "Data",
    "cpa" => "CodigoPostal",
    "codigo_postal" => "CodigoPostal",
    "region_nombre" => "Region",
    "provincia" => "Region",
	"score"=>"Score"
);

//Campos para el listado de solicitudes
const SOLICITUDES_LIST = array(
	"estado_solicitud" => "Estado",
	"prestamo_id" => "Prestamo",
	"persona_id" => "Persona",
	"data" => "Datos",
	"origen" => "Origen",
	"producto_id" => "producto",
);

//Campos para el listado de planes
const PLANES_LIST = array(
	"tipo" => "Tipo",
	"nombre_comercial" => "Nombre",
	"esdefault" => "Omision",
	"devengamiento" => "Devengamiento",
	"estado" => "Estado",
	"tipo_moneda" => "Moneda",
	"alias"=>"Alias"
);