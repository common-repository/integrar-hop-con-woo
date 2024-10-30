<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$webhook = site_url() .'/?wc-api=hopenviosreturn'; 
 
return array(
  'packing'           => array(
      'title'           => __( 'Todos los campos son OBLIGATORIOS', 'woocommerce-shipping-hopenvios' ),
      'type'            => 'title',
      'description'     => __( '1- Si tiene alguna duda o consulta respecto al funcionamiento del plugin, no dude en enviarnos un correo a <strong>soporte.sis.hop@hopenvios.com.ar</strong>. 
      </br> 2- Si NO tiene cuenta, puede registrarse <a href="https://hopenvios.com.ar/hop-para-empresas" target="_blank">desde este enlace.</a>.
      </br> 3- Recuerde que las etiquetas se generan dentro de cada pedido, haciendo clic en Generar Etiqueta dentro de la pestaña Detalles HOP. (debe tener el método de envío HOP).
      </br> 4- Los productos deben tener peso (KG) y dimensiones (CM) para que HOP pueda calcular los costos.
      </br> 5- Su envío puede pesar hasta 30 kilos para ser admitido. Las medidas máximas permitidas son hasta 120cm. La suma de los lados no debe superar los 260cm.
      ', 'woocommerce-shipping-hopenvios' ),
	),
	'enabled'           => array(
		'title'           => __( 'Activar HOP Envíos', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'checkbox',
		'label'           => __( 'Activar este método de envío.', 'woocommerce-shipping-hopenvios' ),
		'default'         => 'no'
	),
	'title'             => array(
		'title'           => __( 'Título', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Controla el título que el usuario ve dentro del proceso de pago.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( 'HOP Envíos', 'woocommerce-shipping-hopenvios' ),
		'desc_tip'        => true
	),
  'api'              => array(
		'title'           => __( 'Configuración de la cuenta', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'title',
		'description'     => __( '', 'woocommerce-shipping-hopenvios' ),
  ),
  'cp_origen'         => array(
		'title'           => __( 'Código Postal', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Ingresar código postal de origen.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),
  'nombre'         => array(
		'title'           => __( 'Nombre y Apellido', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Ingresar nombre y apellido o nombre de la empresa.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),
  'dni'         => array(
		'title'           => __( 'DNI', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Ingresar DNI o CUIT de la empresa.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),	
  'destino_dni' 	=> array(
		'title'           => __( 'Campo DNI destino', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Ingresar ID del campo personalizado. EJ: _billing_dni en caso de dejarlo vacío, se toma el _billing_company', 'woocommerce-shipping-hopenvios' ),
		'default'         => '',
  ),	 
  'telefono'         => array(
		'title'           => __( 'Teléfono', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Ingresar teléfono de la empresa.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),  
  'client_id'         => array(
		'title'           => __( 'ClientID', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Ingresar ClientID - Dato provisto por HOP.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),
  'client_secret'         => array(
		'title'           => __( 'ClientSecret', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'password',
		'description'     => __( 'Ingresar ClientSecret - Dato provisto por HOP.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),  
  'email'         => array(
		'title'           => __( 'Email', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Ingresar email - Dato provisto por HOP.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),	
  'password'     => array(
		'title'           => __( 'Password', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'password',
		'description'     => __( 'Ingresar Password - Dato provisto por HOP.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),	
  'sellercode'     => array(
		'title'           => __( 'Seller Code', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Ingresar Seller Code - Dato provisto por HOP.', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),	
	'tiempoprep'           => array(
		'title'           => __( 'Agregar campo de Tiempo de preparación del Producto', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'checkbox',
		'label'           => __( 'Se incorpora un campo que permite determinar el tiempo de preparación de cada producto. (Configurar dentro de cada producto)', 'woocommerce-shipping-hopenvios' ),
		'default'         => 'no'
	),
  'ajusteprecio'     => array(
		'title'           => __( 'Ajustar valor envío', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'description'     => __( 'Permite agregar o restar un % o valor fijo al valor del envío. EJ: para porcentajes, indicar el número con el signo % (50%)', 'woocommerce-shipping-hopenvios' ),
		'default'         => __( '', 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( '', 'meta-box' ),
  ),	
  'url_key'     => array(
		'title'           => __( 'URL PARA NOTIFICACIONES', 'woocommerce-shipping-hopenvios' ),
		'type'            => 'text',
		'default'         => __( $webhook, 'woocommerce-shipping-hopenvios' ),
    'placeholder' => __( $webhook, 'meta-box' ),
    'custom_attributes' => array(
      'readonly' => 'readonly'
     )
  ),	
   
);
