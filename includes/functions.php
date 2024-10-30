<?php
 
  define('HOP_DIR_PATH',plugin_dir_path(__FILE__));
  define('HOP_DIR_URL',plugin_dir_url(__FILE__));
  define('HOP_SLUG_NAME','hop');
  define('HOP_TEXT_NAME','HOP ENVIOS');
 

  //add_action( "woocommerce_checkout_update_order_meta", "order_sucursal_main_update_order_meta_hopenvios", 10 );
  use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
  add_action( 'add_meta_boxes', 'add_hopenvios_metabox' );
  add_action("add_meta_boxes", "woocommerce_hopenvios_box_add_box");
  add_action("wp_ajax_hopenvios_get_etiqueta", "hopenvios_get_etiqueta", 1);
  add_action("wp_ajax_nopriv_hopenvios_get_etiqueta", "hopenvios_get_etiqueta", 1);
  add_action("wp_ajax_hopenvios_imponer", "hopenvios_imponer", 1);
  add_action("wp_ajax_nopriv_hopenvios_imponer", "hopenvios_imponer", 1);
  add_action("wp_ajax_hopenvios_get_rate", "hopenvios_get_rate", 1);
  add_action("wp_ajax_nopriv_hopenvios_get_rate", "hopenvios_get_rate", 1);
  add_action("woocommerce_api_hopenviosreturn", "callback_handler_hopenvios");
  add_action("woocommerce_after_order_notes", "hop_order_sucursal_main");
  add_action("wp_ajax_hop_check_sucursales", "hop_check_sucursales", 1);
  add_action("wp_ajax_nopriv_hop_check_sucursales", "hop_check_sucursales", 1);
  add_action("wp_footer", "hop_checkout_extra");
  add_action("woocommerce_checkout_update_order_meta", "order_sucursal_main_update_order_meta_hop");
  add_action("woocommerce_checkout_process", "checkout_field_hop_process");
  add_action('wp_ajax_save_sucursal_hop', 'save_sucursal_hop', 1);
  add_action('wp_ajax_nopriv_save_sucursal_hop', 'save_sucursal_hop', 1);
  add_action('woocommerce_product_options_shipping', 'custom_hop_settings_field');
  add_action('woocommerce_process_product_meta', 'save_custom_hop_settings_field');

  function save_sucursal_hop(){
    WC()->session->set( 'sucursal_hop' , $_POST['sucuhop'] );
  }

  function checkout_field_hop_process() {
      global $woocommerce;
      $sucursal_hop = WC()->session->get( 'sucursal_hop' );
      $chosen_methods = WC()->session->get("chosen_shipping_methods");
      $chosen_shipping = $chosen_methods[0];
      $_SESSION["chosen_shippinghop"] = $chosen_shipping;
      if (strpos($chosen_shipping, "hopenvios_sucursal") !== false) {
          if (empty($sucursal_hop)) { 
              wc_add_notice( __("Por favor, seleccionar un punto de retiro."), "error" );
          }
      }
  }

  function order_sucursal_main_update_order_meta_hop($order_id) {
      session_start();
      $chosen_shipping = json_encode($_SESSION["chosen_shippinghop"]);
      $sucursal_hop = WC()->session->get( 'sucursal_hop' );
      $params_hop = json_encode($_SESSION["params_hop"]);

      $order = wc_get_order( $order_id );
      $order->update_meta_data( "hop_estandar", $sucursal_hop );
      $order->update_meta_data( "_origen_datoshop", $_SESSION["origen_datoshop"] );
      $order->update_meta_data( "_chosen_shippinghop", $chosen_shipping );

      $order->save();

  }

  function callback_handler_hopenvios() {
      header("HTTP/1.1 200 OK");
      die();
  }

  function hop_check_sucursales() {
      global $wp_session;

      if (isset($_POST["post_code"])) {
 
 		$recoveredData = file_get_contents(plugin_dir_path(__FILE__).'puntos.json');
 		$pickup_points = json_decode($recoveredData);
 
 		echo '<select id="pv_centro_hop_estandar" name="pv_centro_hop_estandar">';

          $listado_hop = [];

          foreach ($pickup_points->data as $sucursales) {
              if ($sucursales->zip_code == $_POST["post_code"]) {
                  echo '<option value="' .
                      $sucursales->id .
                      '">' .
                      $sucursales->reference_name .
                      " - " .
                      $sucursales->full_address .
                      " - " .
                      $sucursales->city .
                      "</option>";
              }
          }

          echo "</select>";

          die();
      }
  }

  function hop_order_sucursal_main($checkout) {
      global $woocommerce, $wp_session;

      echo '<div id="order_sucursal_mainhop" style="display:none; margin-bottom: 50px;"><img class="hop-logo" src="' . plugins_url("assets/img/logo.png", __FILE__) . '"></br></br></br>';
      echo "<small>Elegí tu punto HOP en el listado.</small>";
      echo '<div id="order_sucursal_mainhop_result_cargando">Cargando Puntos...';
      echo "</div>";
      echo '<div id="order_sucursal_mainhop_result" style="display:none;">Cargando Puntos...';
      echo "</div>";
      echo "</div>";
  }

  function hop_checkout_extra() {
      if (is_checkout()) { ?>
      <script type="text/javascript">
          jQuery(document).ready(function () {  
          jQuery('#order_sucursal_mainhop').insertAfter( jQuery( '.woocommerce-checkout-review-order-table' ) );
          jQuery('#calc_shipping_postcode').attr({ maxLength : 4 });
          jQuery('#billing_postcode').attr({ maxLength : 4 });
          jQuery('#shipping_postcode').attr({ maxLength : 4 });

                jQuery("#calc_shipping_postcode").keypress(function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                  return false;
                }
                });
                jQuery("#billing_postcode").keypress(function (e) { 
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) { 
                return false;
                }
                });
                jQuery("#shipping_postcode").keypress(function (e) {  
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
                }
                });
                
 
              jQuery('#billing_postcode').focusout(function () {
                if (jQuery('#ship-to-different-address-checkbox').is(':checked')) {
                  var state = jQuery('#shipping_state').val();
                  var post_code = jQuery('#shipping_postcode').val();
                } else {
                  var state = jQuery('#billing_postcode').val();
                  var post_code = jQuery('#billing_postcode').val();
                }

                var selectedMethod = jQuery('input:checked', '#shipping_method').attr('id');
                var selectedMethodb = jQuery( "#order_review .shipping .shipping_method option:selected" ).val();
                if (selectedMethod == null) {
                    if(selectedMethodb != null){
                      selectedMethod = selectedMethodb;
                    } else {
                      if (selectedMethod == null || selectedMethod == undefined) {
 							var selectedMethod = jQuery('#shipping_method_0_hopenvios_sucursal').val();
                    }
                    }
                }	 					
                
                                        


                if (selectedMethod.indexOf("_hopenvios_sucursal") >= 0 || selectedMethod == 'hopenvios_sucursal') {
                
 
                  jQuery("#order_review #order_sucursal_mainhop_result").fadeOut(100);
                  jQuery("#order_review #order_sucursal_mainhop_result_cargando").fadeIn(100);	
                  jQuery.ajax({
                    type: 'POST',
                    cache: false,
                    url: wc_checkout_params.ajax_url,
                    data: {
                      action: 'hop_check_sucursales',
                      post_code: post_code,
                    },
                    success: function(data, textStatus, XMLHttpRequest){
 
                          jQuery("#order_review #order_sucursal_mainhop").fadeIn(100);
                          jQuery("#order_review #order_sucursal_mainhop_result").fadeIn(100);
                          jQuery("#order_review #order_sucursal_mainhop_result_cargando").fadeOut(100);	
                          jQuery("#order_review #order_sucursal_mainhop_result").html('');
                          jQuery("#order_review #order_sucursal_mainhop_result").append(data);

                          var selectList = jQuery('#order_review #pv_centro_hop_estandar option');
                          var arr = selectList.map(function(_, o) { return { t: jQuery(o).text(), v: o.value }; }).get();
                          arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
                          selectList.each(function(i, o) {
                            o.value = arr[i].v;
                            jQuery(o).text(arr[i].t);
                          });
                          jQuery('#order_review #pv_centro_hop_estandar').html(selectList);
                          jQuery("#order_review #pv_centro_hop_estandar").prepend("<option value='0' selected='selected'>Sucursales Disponibles</option>");

                        },
                        error: function(MLHttpRequest, textStatus, errorThrown){ }
                      });
                    return false;	

                } else {
                  jQuery('#order_review #order_sucursal_mainhop').hide();  
                }


          });
          });


          function toggleCustomBox() {
 				var selectedMethod = jQuery('input:checked', '#shipping_method').attr('id');
                var selectedMethodb = jQuery( "#order_review .shipping .shipping_method option:selected" ).val();
                
                if (selectedMethod == null) {
                    if(selectedMethodb != null){
                      selectedMethod = selectedMethodb;
                    }  
                }	 					
                
                if (  selectedMethod == undefined) {
                       		var selectedMethod = 'hopenvios_sucursal';
                 }                        
 
                if (selectedMethod.indexOf("_hopenvios_sucursal") >= 0 || selectedMethod == 'hopenvios_sucursal') {

                    jQuery('#order_sucursal_mainhop').show();
                    jQuery('#order_sucursal_mainhop').insertAfter( jQuery('.shop_table') );

                    if (jQuery('#ship-to-different-address-checkbox').is(':checked')) {
                      var state = jQuery('#shipping_state').val();
                      var post_code = jQuery('#shipping_postcode').val();
                    } else {
                      var state = jQuery('#billing_postcode').val();
                      var post_code = jQuery('#billing_postcode').val();
                    }

                    var order_sucursal = 'ok';

                    jQuery("#order_sucursal_mainhop_result").fadeOut(100);
                    jQuery("#order_sucursal_mainhop_result_cargando").fadeIn(100);	
                    jQuery.ajax({
                      type: 'POST',
                      cache: false,
                      url: wc_checkout_params.ajax_url,
                      data: {
                        action: 'hop_check_sucursales',
                        post_code: post_code,
                      },
                      success: function(data, textStatus, XMLHttpRequest){
                            jQuery("#order_sucursal_mainhop").fadeIn(100);
                            jQuery("#order_sucursal_mainhop_result").fadeIn(100);
                            jQuery("#order_sucursal_mainhop_result_cargando").fadeOut(100);	
                            jQuery("#order_sucursal_mainhop_result").html('');
                            jQuery("#order_sucursal_mainhop_result").append(data);

                            var selectList = jQuery('#pv_centro_hop_estandar option');
                            var arr = selectList.map(function(_, o) { return { t: jQuery(o).text(), v: o.value }; }).get();
                            arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
                            selectList.each(function(i, o) {
                              o.value = arr[i].v;
                              jQuery(o).text(arr[i].t);
                            });
                            jQuery('#pv_centro_hop_estandar').html(selectList);
                            jQuery("#pv_centro_hop_estandar").prepend("<option value='0' selected='selected'>Sucursales Disponibles</option>");

                          },
                          error: function(MLHttpRequest, textStatus, errorThrown){alert(errorThrown);}
                      });
                    return false;					

                  } else {
                    jQuery('#order_sucursal_mainhop').hide();  
                  }
          }; //ends toggleCustomBox

           jQuery(document).ready(toggleCustomBox);
          jQuery(document).on('change', '#shipping_method input:radio', toggleCustomBox);
          jQuery(document).on('change', '#order_review .shipping .shipping_method', toggleCustomBox);


          jQuery(document).on('change','#pv_centro_hop_estandar',function(){
            var sucuhop =  jQuery(this).find("option:selected").attr('value')  ;       

            jQuery.ajax({
                    type: 'POST',
                    cache: false,
                    url: wc_checkout_params.ajax_url,
                    data: {
                        action: 'save_sucursal_hop',
                        sucuhop: sucuhop
                    },
                    success: function(data, textStatus, XMLHttpRequest){

                          },
                          error: function(MLHttpRequest, textStatus, errorThrown){}
            });
          });

        </script>


        <style type="text/css">
           #order_sucursal_mainhop h3 {
              text-align: left;
              padding: 5px 0 5px 115px;
          }
          .hop-logo {
            position: absolute;
            margin: 0px;
          }
        </style>
      <?php }
  }

  function hopenvios_handle_callback() {
      global $woocommerce;

      http_response_code(200);
      header("HTTP/1.1 200 OK");
      header("Status: 200 All rosy");
      exit();
  }

  function woocommerce_hopenvios_box_add_box() {
      add_meta_box( "woocommerce-hopenvios-box", __("Detalles HOP", "woocommerce-hopenvios"), "woocommerce_hopenvios_box_create_box_content", "shop_order", "side", "default"     );
  }

  function add_hopenvios_metabox() {
    $screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
      ? wc_get_page_screen_id( 'shop-order' )
      : 'shop_order';

    add_meta_box(
      'hopenvios',
      'Detalles HOP',
      'render_hopenvios_metabox',
      $screen,
      'side',
      'high'
    );
  }
 
  function render_hopenvios_metabox( $post_or_order_object ) {
    global $post;
  
    $order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
     
      $site_url = get_site_url();
   
      $hopenvios_settings = get_post_meta($post_or_order_object->ID, "hopenvios_settings", true);
      $settings_etiquetas = get_post_meta($post_or_order_object->ID, "settings_etiquetas", true);
      $shipping = $order->get_items("shipping");
      $hopenvios_settings = json_decode($hopenvios_settings, true);

      $transaccionid = get_post_meta($post_or_order_object->ID, "_hopenvios_transaccionid", true);
      $codigotransaccion = get_post_meta( $post_or_order_object->ID, "_hopenvios_codigotransaccion", true );

      echo '<div class="hopenvios-single">';
      echo "<strong>Modalidad de envío</strong></br>";
      foreach ($shipping as $method) {
          if ($method["method_id"] == "hopenvios_wanderlust") {
              echo $method["name"] . "</br></br>";
              $shipping_settings_id = $method["instance_id"];
          }
      }

      echo "</div>";

      //ETIQUETA
      $hopenvios_shipping_label_tracking = get_post_meta( $post_or_order_object->ID, "_tracking_number", true );
      $hopenvios_shipping_label_tracking_link = get_post_meta( $post_or_order_object->ID, "_custom_tracking_link", true );
      $etiqueta_url = get_post_meta($post_or_order_object->ID, "_hopenvios_etiqueta_url", true);
      $hopenvios_manual = get_post_meta($post_or_order_object->ID, "_hopenvios_manual", true);
      $hop_envios = get_post_meta($post_or_order_object->ID, "hop_envios", true);

      if ( empty($hopenvios_shipping_label_tracking) || empty($etiqueta_url) || empty($hop_envios) ) { ?>

          <style type="text/css">
            #generar-hopenvios, #editar-hopenvios, #manual-hopenvios-generar, #obtener-a-hopenvios, #obtener-m-hopenvios  {
              background: #00acb6;
              color: white;
              width: 100%;
              text-align: center;
              height: 40px;
              padding: 0px;
              line-height: 37px;
              margin-top: 20px;
              clear:both;
            }
            #editar-hopenvios {
              background: #d24040;
            }
            #manual-hopenvios {
              display:none;
            }

          </style>

          <img id="hopenvios_loader" style="display:none; max-width: 65px; height: auto; margin: 10px 95px; position: relative;" src="<?php echo plugin_dir_url( __FILE__ ) . "assets/img/logo.png"; ?>">

          <?php echo '<div id="obtener-a-hopenvios" class="button" data-id="' . $post_or_order_object->ID . '">Obtener Etiqueta</div>'; ?>


          <div class="hopenvios-single-label"> </div>	

          <script type="text/javascript">		

            jQuery('body').on('click', '#obtener-a-hopenvios',function(e){ 
              e.preventDefault();
              var ajaxurl = "<?php echo admin_url("admin-ajax.php"); ?>";
              var dataid = jQuery(this).data("id");
              jQuery(this).hide();
              jQuery('#hopenvios_loader').fadeIn();
              jQuery.ajax({
                type: 'POST',
                cache: false,
                url: ajaxurl,
                data: {action: 'hopenvios_imponer',dataid: dataid},
                success: function(data, textStatus, XMLHttpRequest){ 
                  jQuery('#hopenvios_loader').fadeOut();
                  jQuery(".hopenvios-single-label").fadeIn(400);
                  jQuery(".hopenvios-single-label").html('');
                  jQuery(".hopenvios-single-label").append(data);
                  //lhoption.reload();
                },
                error: function(MLHttpRequest, textStatus, errorThrown){ }
              });
            });					

          </script>
        <?php } else {echo '<div  style="position: relative; width: 100%; height: 60px;" ><a style=" width: 225px; text-align: center;background: #643494;color: white;padding: 10px;margin: 10px;float: left;text-decoration: none;" href="' . $etiqueta_url . '" target="_blank">IMPRIMIR ETIQUETA</a></div>';}
  
    
  }

  function woocommerce_hopenvios_box_create_box_content() {
      global $post;
      $site_url = get_site_url();
      $order = wc_get_order($post->ID);
      $hopenvios_settings = get_post_meta($post->ID, "hopenvios_settings", true);
      $settings_etiquetas = get_post_meta($post->ID, "settings_etiquetas", true);
      $shipping = $order->get_items("shipping");
      $hopenvios_settings = json_decode($hopenvios_settings, true);

      $transaccionid = get_post_meta($post->ID, "_hopenvios_transaccionid", true);
      $codigotransaccion = get_post_meta( $post->ID, "_hopenvios_codigotransaccion", true );

      echo '<div class="hopenvios-single">';
      echo "<strong>Modalidad de envío</strong></br>";
      foreach ($shipping as $method) {
          if ($method["method_id"] == "hopenvios_wanderlust") {
              echo $method["name"] . "</br></br>";
              $shipping_settings_id = $method["instance_id"];
          }
      }

      echo "</div>";

      //ETIQUETA
      $hopenvios_shipping_label_tracking = get_post_meta( $post->ID, "_tracking_number", true );
      $hopenvios_shipping_label_tracking_link = get_post_meta( $post->ID, "_custom_tracking_link", true );
      $etiqueta_url = get_post_meta($post->ID, "_hopenvios_etiqueta_url", true);
      $hopenvios_manual = get_post_meta($post->ID, "_hopenvios_manual", true);
      $hop_envios = get_post_meta($post->ID, "hop_envios", true);

      if ( empty($hopenvios_shipping_label_tracking) || empty($etiqueta_url) || empty($hop_envios) ) { ?>

          <style type="text/css">
            #generar-hopenvios, #editar-hopenvios, #manual-hopenvios-generar, #obtener-a-hopenvios, #obtener-m-hopenvios  {
              background: #00acb6;
              color: white;
              width: 100%;
              text-align: center;
              height: 40px;
              padding: 0px;
              line-height: 37px;
              margin-top: 20px;
              clear:both;
            }
            #editar-hopenvios {
              background: #d24040;
            }
            #manual-hopenvios {
              display:none;
            }

          </style>

          <img id="hopenvios_loader" style="display:none; max-width: 65px; height: auto; margin: 10px 95px; position: relative;" src="<?php echo plugin_dir_url( __FILE__ ) . "assets/img/logo.png"; ?>">

          <?php echo '<div id="obtener-a-hopenvios" class="button" data-id="' . $post->ID . '">Obtener Etiqueta</div>'; ?>


          <div class="hopenvios-single-label"> </div>	

          <script type="text/javascript">		

            jQuery('body').on('click', '#obtener-a-hopenvios',function(e){ 
              e.preventDefault();
              var ajaxurl = "<?php echo admin_url("admin-ajax.php"); ?>";
              var dataid = jQuery(this).data("id");
              jQuery(this).hide();
              jQuery('#hopenvios_loader').fadeIn();
              jQuery.ajax({
                type: 'POST',
                cache: false,
                url: ajaxurl,
                data: {action: 'hopenvios_imponer',dataid: dataid},
                success: function(data, textStatus, XMLHttpRequest){ 
                  jQuery('#hopenvios_loader').fadeOut();
                  jQuery(".hopenvios-single-label").fadeIn(400);
                  jQuery(".hopenvios-single-label").html('');
                  jQuery(".hopenvios-single-label").append(data);
                  //lhoption.reload();
                },
                error: function(MLHttpRequest, textStatus, errorThrown){ }
              });
            });					

          </script>
        <?php } else {echo '<div  style="position: relative; width: 100%; height: 60px;" ><a style=" width: 225px; text-align: center;background: #643494;color: white;padding: 10px;margin: 10px;float: left;text-decoration: none;" href="' . $etiqueta_url . '" target="_blank">IMPRIMIR ETIQUETA</a></div>';}
  }

  function hopenvios_admin_notice() {
      global $wp_session; ?>
        <div class="notice error my-acf-notice is-dismissible" >
            <p><?php print_r($wp_session["hopenvios_notice"]); ?></p>
        </div>
        <?php
}

  function hopenvios_imponer($order_id) {
      if (empty($order_id)) {
          $order_id = $_POST["dataid"];
      }

      $order = wc_get_order($order_id);
    
      $total_weight = 0;
      $max_length = 0;
      $max_width = 0;
      $total_height = 0;
      foreach ($order->get_items() as $item_id => $product_item) {
          $quantity = $product_item->get_quantity();
        
          $product = $product_item->get_product();

          // Ensure the product exists
          if ($product) {
            $length = $product->get_length();
            $width = $product->get_width();
            $height = $product->get_height();
            $weight = $product->get_weight();
            $total_product_weight = $weight * $quantity;
            $total_weight += $total_product_weight;

            $max_length = max($max_length, $length);
            $max_width = max($max_width, $width);
            $total_height += $height * $quantity;
          }


      }
 
      $tipo_envio = "sucursal";
    
      // Get the shipping methods for the order
      $shipping_methods = $order->get_items('shipping');
    
      foreach ($shipping_methods as $method) {
        // Get the instance ID of the shipping method
        $instance_id = $method->get_instance_id();

        // Retrieve the shipping method settings directly from the database
        $option_name = 'woocommerce_hopenvios_wanderlust_' . $instance_id . '_settings';
        $shipping_method_settings = get_option($option_name, true);
        if ($shipping_method_settings) {
          continue;
        }
      }
    
      if($shipping_method_settings["destino_dni"]){
          $billing_dni = get_post_meta($order_id, $shipping_method_settings["destino_dni"], true);
      } else {
          $billing_dni = get_post_meta($order_id, "_billing_company", true);
      }
	  
	  if(empty($billing_dni)){
 		$billing_dni = $order->get_meta( $shipping_method_settings["destino_dni"] );
	  }
      
	  $hop_estandar = $order->get_meta( 'hop_estandar' ); 

 
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
              "client_id" => $shipping_method_settings["client_id"],
              "client_secret" => $shipping_method_settings["client_secret"],
              "email" => $shipping_method_settings["email"],
              "password" => $shipping_method_settings["password"],
          ],
      ]);

      $access_token = curl_exec($curl);
      curl_close($curl);
      $access_token = json_decode($access_token);

      $body = [
          "shipping_type" => "E",
          "reference_id" => "WOO-" . $order_id,
          "reference_1" => "WOO-" . $order_id,
          "reference_2" => "",
          "reference_3" => "",
          "label_type" => "JPEG",
          "seller_code" => $shipping_method_settings["sellercode"],
          "storage_code" => "DEPOSITO",
          "days_offset" => "0",
          "validate_client_id" => "0",
          "pickup_point_id" => $hop_estandar,
          "client" => [
              "name" => $order->get_billing_first_name() . " " . $order->get_billing_last_name(),
              "email" => $order->get_billing_email(),
              "id_type" => "D.N.I",
              "id_number" => $billing_dni,
              "telephone" => $order->get_billing_phone(),
          ],
          "package" => [
              "size_category" => "1",
              "value" => $order->get_total(),
              "weight" => $total_weight,
          ]
      ];
      
      
 
      $body = wp_json_encode($body);
      $options = [
          "body" => $body,
          "headers" => [
              "Authorization" => "Bearer " . $access_token->access_token,
              "Content-Type" => "application/json",
          ],
          "timeout" => 60,
          "redirection" => 5,
          "blocking" => true,
          "httpversion" => "1.0",
          "sslverify" => false,
          "data_format" => "body",
      ];

      $respuesta_api = wp_remote_post( "https://api.hopenvios.com.ar/api/v1/shipping", $options );

      if (!is_wp_error($respuesta_api)) {
         
          $respuesta = json_decode($respuesta_api["body"]);

          $date = strtotime(date("Y-m-d"));
        
          if($respuesta->security_code){
            $order->add_order_note( "HOP COD SEGURIDAD: " . $respuesta->security_code );
            $order->add_order_note( "HOP SHIPPING ID: " . $respuesta->shipping_id);
            $order->add_order_note( "HOP TRACKING: " . $respuesta->tracking_nro);
            $order->add_order_note( "HOP ETIQUETA: " . $respuesta->label_url);    
            $order->update_meta_data( "_tracking_number", $respuesta->tracking_nro );
            $order->update_meta_data( "_custom_tracking_link", "https://hopenvios.com.ar/segui-tu-envio?c=" . $respuesta->tracking_nro );
            $order->update_meta_data( "_hopenvios_etiqueta_url", $respuesta->label_url );
            echo '<div  style="position: relative; width: 100%; height: 60px;" ><a style=" width: 225px; text-align: center;background: #643494;color: white;padding: 10px;margin: 10px;float: left;text-decoration: none;" href="' . $respuesta->label_url . '" target="_blank">IMPRIMIR ETIQUETA</a></div>';
          } else {
            echo '<pre>ERROR: ';print_r($respuesta_api["body"]);echo'</pre>'; 
          }
 
          $order->update_meta_data( "hop_envios", $respuesta_api["body"] );

          $order->save();

          die();
      }
  }

  function get_wc_shipping_method_by_instance_id($instance_id) {
      // Get all shipping zones including the default one (zone ID 0)
      $zones = WC_Shipping_Zones::get_zones();
      $zones[] = array('id' => 0, 'zone_name' => 'Rest of the World', 'shipping_methods' => WC_Shipping_Zones::get_zone(0)->get_shipping_methods());

      foreach ($zones as $zone) {
          foreach ($zone['shipping_methods'] as $method) {
              // Check if the instance ID matches
              if ($method->get_instance_id() == $instance_id) {
                  return $method;
              }
          }
      }

      return false;
  }

  function custom_hop_settings_field() {
      global $post;
    
      $tiempoprep = get_option('tiempoprep');
    
      if($tiempoprep == 'yes'){
        echo '<div class="options_group">';

        woocommerce_wp_text_input(
            array(
                'id' => '_custom_hop_text_field',
                'label' => __('HOP - Tiempo de preparación', 'woocommerce'),
                'description' => __('Ingresar cantidad en dias.', 'woocommerce'),
                'desc_tip' => true,
                'type' => 'number',
            )
        );

        echo '</div>';      
      }
 

  }

  function save_custom_hop_settings_field($post_id) {
      $custom_shipping_text = isset($_POST['_custom_hop_text_field']) ? sanitize_text_field($_POST['_custom_hop_text_field']) : '';
      update_post_meta($post_id, '_custom_hop_text_field', $custom_shipping_text);
  }

	 
	add_filter('cron_schedules', 'hoppuntos_cron_schedule');
	function hoppuntos_cron_schedule($schedules) {
		$schedules['every_sixty_minutes'] = array(
			'interval' => 3600,  
			'display'  => __('Every 60 Minutes')
		);
		return $schedules;
	}

	if (!wp_next_scheduled('hoppuntos_api_check_event')) {
		wp_schedule_event(time(), 'every_sixty_minutes', 'hoppuntos_api_check_event');
	}

	add_action('hoppuntos_api_check_event', 'check_hoppuntos_api');

	function check_hoppuntos_api() {
		
		 $delivery_zones = WC_Shipping_Zones::get_zones();

          foreach ($delivery_zones as $zones) {
              foreach ($zones["shipping_methods"] as $methods) {
                  if ($methods->id == "hopenvios_wanderlust") {
                      if ($methods->enabled == "yes") {
                          $client_id = $methods->instance_settings["client_id"];
                          $client_secret = $methods->instance_settings["client_secret"];
                          $email = $methods->instance_settings["email"];
                          $password = $methods->instance_settings["password"];
                      }
                  }
              }
          }

          $curl = curl_init();
		
		  if($client_id){
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
					  "client_id" => $client_id,
					  "client_secret" => $client_secret,
					  "email" => $email,
					  "password" => $password,
				  ],
			  ]);

			  $access_token = curl_exec($curl);
			  curl_close($curl);

			  $access_token = json_decode($access_token);
			  
			  if($access_token){
				  $curl = curl_init();

				  curl_setopt_array($curl, [
					  CURLOPT_URL => "https://api.hopenvios.com.ar/api/v1/pickup_points?postal_codes=&sort_by=zip_code",
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 0,
					  CURLOPT_FOLLOWLOCATION => true,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "GET",
					  CURLOPT_HTTPHEADER => [
						  "Authorization: Bearer " . $access_token->access_token,
					  ],
				  ]);

				  $pickup_points = curl_exec($curl);

				  curl_close($curl);		
 				 
				  file_put_contents(plugin_dir_path(__FILE__).'puntos.json', $pickup_points);
 
			  }
  
		  
		  }
 
	}

	register_deactivation_hook(__FILE__, 'deactivate_hoppuntos_cron');
	function deactivate_hoppuntos_cron() {
		$timestamp = wp_next_scheduled('hoppuntos_api_check_event');
		wp_unschedule_event($timestamp, 'hoppuntos_api_check_event');
	}


?>