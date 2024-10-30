<?php
if (!defined("ABSPATH")) {
    exit();
}
 
class WC_Shipping_HOPEnvios extends WC_Shipping_Method {
     public function __construct($instance_id = 0) {
        $this->id = "hopenvios_wanderlust";
        $this->instance_id = absint($instance_id);
        $this->method_title = __(
            "HOP Envios",
            "woocommerce-shipping-hopenvios"
        );
        $this->method_description = __(
            "HOP Envios te permite cotizar el valor de un envío con una amplia cantidad de empresas de correo de una forma simple y estandarizada.",
            "woocommerce"
        );
        $this->supports = ["shipping-zones", "instance-settings"];

        $this->init();

        add_action("woocommerce_update_options_shipping_" . $this->id, [
            $this,
            "process_admin_options",
        ]);
    }
  
     public function init() {
        $this->init_form_fields = include "data/data-settings.php";
        $this->init_settings();
        $this->instance_form_fields = include "data/data-settings.php";

        $this->title = $this->get_option("title", $this->method_title);
        $this->cp_origen = $this->get_option("cp_origen");
        $this->client_id = $this->get_option("client_id");
        $this->client_secret = $this->get_option("client_secret");
        $this->email = $this->get_option("email");
        $this->password = $this->get_option("password");
        $this->test_enabled = $this->get_option("test_enabled");
        $this->destino_dni = $this->get_option("destino_dni");
        $this->url_key = $this->get_option("url_key");
        $this->sellercode = $this->get_option("sellercode");
        $this->ajusteprecio = $this->get_option("ajusteprecio");
        $this->tiempoprep = $this->get_option("tiempoprep");
       
        update_option('tiempoprep', $this->tiempoprep);
       
    }
 
     public function admin_options() {
         parent::admin_options();
    }
 
     public function calculate_shipping($package = []) {
        global $wp_session, $woocommerce;
         
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.hopenvios.com.ar/api/v1/login",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => [
                "client_id" => $this->client_id,
                "client_secret" => $this->client_secret,
                "email" => $this->email,
                "password" => $this->password,
            ],
        ]);

        $access_token = curl_exec($curl);
        curl_close($curl);
        $access_token = json_decode($access_token);

        $dimension_unit = esc_attr(get_option("woocommerce_dimension_unit"));
        $weight_unit = esc_attr(get_option("woocommerce_weight_unit"));
        $weight_multi = 0;
        $dimension_multi = 0;
        if ($dimension_unit == "m") {
            $dimension_multi = 100;
        }
        if ($dimension_unit == "cm") {
            $dimension_multi = 1;
        }
        if ($dimension_unit == "mm") {
            $dimension_multi = 0.1;
        }
        if ($weight_unit == "kg") {
            $weight_multi = 1000;
        }
        if ($weight_unit == "g") {
            $weight_multi = 1;
        }

        $cart = $woocommerce->cart;
        $items = $woocommerce->cart->get_cart();

        $articulos = [];
        $productidweights = 0;
      
        $tiempopreparacion = 0;

        foreach ($items as $item => $values) {
            $_product = wc_get_product($values["product_id"]);
            $productidweights += $_product->get_weight() * $values["quantity"];
            $custom_hop_text = get_post_meta($values["product_id"], '_custom_hop_text_field', true);
            $tiempopreparacion = max($tiempopreparacion, $custom_hop_text);
            $i = 0;
            $array = [];
            while ($i++ < $values["quantity"]) {
                $articulos[] = [
                    "quantity" => 1,
                    "declaredValue" => $_product->get_price(),
                    "sizeHeight" => $_product->get_height() * $dimension_multi,
                    "sizeWidth" => $_product->get_width() * $dimension_multi,
                    "sizeDepth" => $_product->get_length() * $dimension_multi,
                    "weight" => $_product->get_weight(),
                    "weightUnit" => strtoupper($weight_unit),
                ];
            }
        }
 
        $total_weight = 0;
        $max_length = 0;
        $max_width = 0;
        $total_height = 0;

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $length = $product->get_length() * $dimension_multi;
            $width = $product->get_width() * $dimension_multi;
            $height = $product->get_height() * $dimension_multi;
            $weight = $product->get_weight() * $weight_multi;
 
            $quantity = $cart_item['quantity'];
            $total_product_weight = $weight * $quantity;
            $total_weight += $total_product_weight;

            $max_length = max($max_length, $length);
            $max_width = max($max_width, $width);
            $total_height += $height * $quantity;
        }

        $cpdestino = $package["destination"]["postcode"];
         
        $recoveredData = file_get_contents(plugin_dir_path(__FILE__).'puntos.json');
        $pickup_points = json_decode($recoveredData);
 
 
        $existe = 'no';
        foreach ($pickup_points->data as $sucursales) {
            if ($sucursales->zip_code == $cpdestino) {
                $existe = 'si';
            }
        }

        if ($cpdestino &&  $existe == 'si' ) {
            $valor_envio = WC()->cart->cart_contents_total;
           
            $urlcostos = 'https://api.hopenvios.com.ar/api/v1/pricing/estimate?origin_zipcode='.$this->cp_origen.'&destiny_zipcode='.$cpdestino.'&shipping_type=E&package[value]='.$valor_envio.'&package[height]='.$total_height.'&package[length]='.$max_length.'&package[width]='.$max_width.'&seller_code='.$this->sellercode.'&package[weight]='.$total_weight.'';
            $response = wp_remote_get($urlcostos, [
                "timeout" => 120,
                "httpversion" => "1.1",
                "headers" => [
                    "Content-Type" => "application/json",
                    "Authorization" => " Bearer " . $access_token->access_token,
                ],
            ]);
 
            if (is_array($response) && !is_wp_error($response)) {  
                $headers = $response["headers"];
                $body = $response["body"];

                $respuesta = json_decode($body);
              
                if ($this->ajusteprecio) {
                    $ajuste = $this->ajusteprecio;
                    if (strpos($ajuste, '%') !== false) {
                        $percentage = floatval(str_replace('%', '', $ajuste));
                        $extra_amount = $respuesta->data->amount * ($percentage / 100);
                        $respuesta->data->amount += $extra_amount;
                    } else {
                        $extra_amount = floatval($ajuste);
                        $respuesta->data->amount += $extra_amount;
                    }
                }
           
                if($this->tiempoprep == 'yes' && $tiempopreparacion > 1 ){
                  $titulo = $this->title .' - (Tiempo de preparación: ' . $tiempopreparacion .' días)';
                } else {
                  $titulo = $this->title;
                }
 
                $rate = [
                    "id" => sprintf("%s", "hopenvios_sucursal"),
                    "label" => sprintf("%s", "$titulo"),
                    "cost" => $respuesta->data->amount,
                    "calc_tax" => "per_order",
                    "package" => $package,
                ];

                $this->add_rate($rate);
            }
        }
    }
}
