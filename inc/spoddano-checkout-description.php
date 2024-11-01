<?php

//Security - exit file if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

add_filter('woocommerce_gateway_description', 'spod_spoddano_description_fields', 20, 2);
add_action('woocommerce_checkout_process', 'spod_spoddano_description_fields_validation');
add_action('woocommerce_checkout_update_order_meta', 'spod_spoddano_checkout_update_order_meta', 10, 1);
add_action('woocommerce_admin_order_data_after_billing_address', 'spod_spoddano_order_data_after_billing_address', 10, 1);
add_action('woocommerce_order_details_after_order_table', 'spod_spoddano_order_items_table', 10, 1 ); 
add_action('woocommerce_email_after_order_table', 'spod_spoddano_email_after_order_table', 10, 4 );
add_action('woocommerce_order_details_before_order_table', 'spod_spoddano_order_details_before_order_table', 10, 1 );



function spod_spoddano_description_fields($description, $payment_id){

        //get the admin settings
        $payment_gateway = WC()->payment_gateways->payment_gateways()['spoddano'];

         //get currency code set in woocommerce
        $currency = get_woocommerce_currency();
        $ccode = $currency;
       
        //apikey from admin settings 
        $cmcAPI = $payment_gateway->apikey;
        //set headers for api key
        $args = array(
            'headers' => array(
              'Content-Type'      => 'application/json',
              'X-CMC_PRO_API_KEY' => $cmcAPI, 
            )
        );
        // Send the request to coinmarketcap, save the response
        $response = wp_remote_get('https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol=ADA&convert='.$ccode, $args);
        $body     = wp_remote_retrieve_body( $response );
        // Decode the Data
        $apiData = json_decode($body,true);
        //set variable for ADA => Store Currency exchange rate & round to 3 decimal points
        $rate = (round($apiData['data']['ADA']['quote'][$ccode]['price'], 3));


    if ('spoddano' !== $payment_id){
        return $description;
    }

    ob_start();

    //get the final total price in the cart 
    $orderTotal = WC()->cart->get_total("spodarg");
  
    // divide the cart total with the Cardano to Store Currency exchange rate and round to 3 decimal points
    $totalADA = (round($orderTotal / $rate, 3)); 
 
    //Begin Front-end Checkout Panel
    _e('<div id="spoddano-field-box">
          <p style="margin:1em 0;"><strong>1) Send the Total Amount in ADA required below, from your wallet to the address below:</strong>
          <p class="spod-total-ada" id="spod_total_ada">Total Amount in ADA to send: <strong>'. $totalADA .'</strong></p>
          <p><small>Todays Exchange Rate: 1 ADA = '. $rate . $ccode .'</small> <small style="color:#898989;">Provided by coinmarketcap.com</small></p>
          <div class="spod-payment-panel">', 'spod-ada-txt');
          if ($payment_gateway->qrcode) :
            _e('<div id="spod-qrcode-panel"><img src="'. $payment_gateway->qrcode .'" width="152" alt="receiver address qrcode" class="spod-qrcode-img"></div>', 'spod-ada-txt');
          endif;
          _e( '<div class="spod-ada-txt-tooltip">
               <a href="javascript:spod_copy_ada();"><span id="spodadatooltip-text">Copy Address</span> <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 19V1H17V5H21V23H7V19H3ZM15 17V3H5V17H15ZM17 7V19H9V21H19V7H17Z" fill="#fff"></path></svg></a> 
           </div>
             <div class="spod-ada-txt-address"> 
             <p id="spodadaaddress">'. $payment_gateway->address .'</textarea>
             <input id="spodadaaddress-hidden" name="spodadaaddress-hidden" type="text" value="'. $payment_gateway->address .'" readonly="true" />   
             </div>
         </div> 

          <p style="margin:3em 0 1em;"><strong>2) Once the transaction is complete, please enter the transaction ID into the below field.</strong>', 'spod-ada-txt');

          woocommerce_form_field(
              'ada_txid_field',
                    array(
                        'type' => 'textarea',
                        'label' => __('Transaction ID', 'spod-ada-txt'),
                        'class' => array('form-row', 'form-row-wide'),
                        'required' => true,
                       )
                    );  
                    _e(  '<input id="spod-total-ada-hidden" name="spod-total-ada-hidden" type="hidden" value="'. $totalADA .'" readonly="true" />    
                 
                  <p style="margin:3em 0 1em;"><strong>3) Click the Place Order button below to submit the order!</strong></p>', 'spod-ada-txt');
                  if ($payment_gateway->disclaimer) :
                    _e('<p class="spod-cc-disclaimer" style="margin:3em 0 1em;"><small><strong>Disclaimer:</strong><br>' . $payment_gateway->disclaimer . '</small></p>', 'spod-ada-txt');
                  endif;
                  _e( '</div>', 'spod-ada-txt');

    $description .= ob_get_clean();

    return $description;


}


//add transaction-id field to order in dashboard
function spod_spoddano_checkout_update_order_meta($order_id){

    if( isset ( $_POST['ada_txid_field']) || ! empty($_POST['ada_txid_field'] ) ) {

        $adatxtfield = sanitize_text_field( $_POST['ada_txid_field'] );
        update_post_meta( $order_id, 'ada_txid_field', $adatxtfield );

    }
    if( isset ( $_POST['spod-total-ada-hidden']) || ! empty($_POST['spod-total-ada-hidden'] ) ) {

        $adatxtfieldhidden = sanitize_text_field( $_POST['spod-total-ada-hidden'] );
        update_post_meta( $order_id, 'spod-total-ada-hidden', $adatxtfieldhidden);
     }
}

//make sure transaction-id field is filled out
function spod_spoddano_description_fields_validation(){

    if( 'spoddano' === $_POST['payment_method'] && ! isset ($_POST['ada_txid_field']) ) {
        wc_add_notice( 'Please enter the Transaction ID', 'error' );
    }
    if( 'spoddano' === $_POST['payment_method'] && empty($_POST['ada_txid_field'] ) ) {
        wc_add_notice( 'Please enter the Transaction ID', 'error' );
    }

}


//show transaction-id field in order in dashboard
function spod_spoddano_order_data_after_billing_address($order){

    _e( '<p><strong>Cardano Transaction ID:</strong><br>'. get_post_meta( $order->get_id(), 'ada_txid_field', true ) .'</p>','spod-ada-txt');
    _e( '<p><strong>Total ADA Sent:</strong><br>'. get_post_meta( $order->get_id(), 'spod-total-ada-hidden', true ) .'</p>','spod-ada-txt');

}

//add thankyou message above order details page
function spod_spoddano_order_details_before_order_table($order){

    _e( '<p><strong>Thank you very much for your order and using Cardano!
        <br>We will confirm the transaction shortly and send you a processing order email.</strong></p>','spod-ada-txt');
}

//add the Cardano Details to the order details thankyou page
function spod_spoddano_order_items_table( $order ) { 
    
    _e( '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details" style="margin-top:0;">
              <thead>
               <tr>
                  <th class="woocommerce-table__product-name product-name">Cardano Transaction ID:</th>
                  <th class="woocommerce-table__product-total product-total">Total ADA Sent:</th>
               </tr>
               </thead>
               <tr class="woocommerce-table__line-item order_item">
                  <td class="woocommerce-table__product-total product-total">'. get_post_meta( $order->get_id(), 'ada_txid_field', true ) .'</td>
                  <td class="woocommerce-table__product-total product-total">'. get_post_meta( $order->get_id(), 'spod-total-ada-hidden', true ) .'</td>
                  </tr></table>','spod-ada-txt');
}

//add the Cardano Details to the email invoices
function spod_spoddano_email_after_order_table( $order, $sent_to_admin, $plain_text, $email ) { 
    _e( '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details" style="margin-top:0;">
    <thead>
     <tr>
        <th class="woocommerce-table__product-name product-name">Cardano Transaction ID:</th>
        <th class="woocommerce-table__product-total product-total">Total ADA Sent:</th>
     </tr>
     </thead>
     <tr class="woocommerce-table__line-item order_item">
        <td class="woocommerce-table__product-total product-total">'. get_post_meta( $order->get_id(), 'ada_txid_field', true ) .'</td>
        <td class="woocommerce-table__product-total product-total">'. get_post_meta( $order->get_id(), 'spod-total-ada-hidden', true ) .'</td>
        </tr></table>','spod-ada-txt');
}

//copy icon address function
function spod_ada_btn() {
    _e( '<script type="text/javascript">
            function spod_copy_ada() {
            var copyText = document.getElementById("spodadaaddress-hidden");
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
            var spodtooltip = document.getElementById("spodadatooltip-text");
            spodtooltip.innerHTML = "Address Copied!";
             //e.preventDefault();
            return false;
            }
        </script>','spod-ada-txt'); 

}
add_action( 'wp_footer', 'spod_ada_btn', 99 );