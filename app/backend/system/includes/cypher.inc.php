<?php
/*
	Funciones de cifrado y descifrado usando AES-256.
	Gracias a Codepath.
	https://guides.codepath.com/websecurity/Symmetric-Key-Algorithms
	
*/
const CIPHER_METHOD = 'AES-256-CBC';

/**
* Summary. Cifra el mensaje con una contraseña usando AES-256 y ofuscando la salida en base64 para que pueda transmitirse el mensaje cifrado a través de HTTP.
* @param str $message. El mensaje.
* @param str $key. La contraseña de cifrado.
* @return str El mensaje cifrado.
*/
function CypherAES($message, $key) {
  // Needs a key of length 32 (256-bit)
  $key = str_pad($key, 32, '*');

  // Create an initialization vector which randomizes the
  // initial settings of the algorithm, making it harder to decrypt.
  // Start by finding the correct size of an initialization vector 
  // for this cipher method.
  $iv_length = openssl_cipher_iv_length(CIPHER_METHOD);
  $iv = openssl_random_pseudo_bytes($iv_length);

  // Encrypt
  $encrypted = openssl_encrypt($message, CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv);

  // Return $iv at front of string, need it for decoding
  $message = $iv . $encrypted;
  
  // Encode just ensures encrypted characters are viewable/savable
  return base64_encode($message);

}

/**
* Summary. Decifra un mensaje usando una contraseña habiendo sido cifrado con AES-256 y ofuscando el mensaje cifrado con base64 para que pueda transmitirse el mensaje cifrado a través de HTTP.
* @param str $message. El mensaje.
* @param str $key. La contraseña de decifrado.
* @return str El mensaje en texto claro.
*/
function DecypherAES($message, $key) {
	$plaintext = '';
	try {
  // Needs a key of length 32 (256-bit)
  $key = str_pad($key, 32, '*');
  // Base64 decode before decrypting
  $iv_with_ciphertext = base64_decode($message);
  // Separate initialization vector and encrypted string
  $iv_length = openssl_cipher_iv_length(CIPHER_METHOD);

  $iv = substr($iv_with_ciphertext, 0, $iv_length);
  // Ensure $iv is at least 16 characters long
	$iv = str_pad($iv,16,"\0");

  $ciphertext = substr($iv_with_ciphertext, $iv_length);


  // Decrypt
  $plaintext = openssl_decrypt($ciphertext, CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv);
	} catch(Exception $e) {
		
	}
  return $plaintext;
}
?>