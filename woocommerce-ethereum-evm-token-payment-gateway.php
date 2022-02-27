<?php
/*
 * Plugin Name: Token Payment Gateway for Ethereum EVM compatible Blockchain ERC20-BEP20 and WooCommerce
 * Version: 2.0.0
 * Plugin URI: https://github.com/AlgoNetwork/Token-Payment-Gateway-for-Ethereum-EVM-compatible-Blockchain-ERC20-BEP20-and-WooCommerce
 * Description: Add Ethereum evm compatible token to your website.
 * Author: jack.
 * Author URI: https://github.com/AlgoNetwork/
 * License: GPLv2 or later
 * Requires at least: 4.7.0
 * Tested up to: 4.9.8
 *
 * Text Domain: woocommerce-ethereum-evm-token-payment-gateway
 * Domain Path: /lang/
 */


if (!defined('ABSPATH')) {
	exit;
}
/**
 * Meta info
 */
add_filter('plugin_row_meta', 'add_link_to_plugin_meta', 10, 4);

function add_link_to_plugin_meta($links_array, $plugin_file_name, $plugin_data, $status) {
	/**
	 * check current plugin is our plugin
	 */
	if (strpos($plugin_file_name, basename(__FILE__))) {
		//  
		// faq
		$links_array[] = '<a href="https://github.com/AlgoNetwork/Token-Payment-Gateway-for-Ethereum-EVM-compatible-Blockchain-ERC20-BEP20-and-WooCommerce">FAQ</a>';
	}
	return $links_array;
}
/**
 * name setting
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'erc20_add_settings_link');
function erc20_add_settings_link($links) {
	$settings_link = '<a href="admin.php?page=wc-settings&tab=checkout">' . __('Settings') . '</a>';
	array_push($links, $settings_link);
	return $links;
}
/**
 *  i18n  localization
 */
add_action('init', 'erc20_load_textdomain');
function erc20_load_textdomain() {
	/**
	 *  
	 */
	load_plugin_textdomain('woocommerce-ethereum-evm-token-payment-gateway', false, basename(dirname(__FILE__)) . '/lang');
}

/**
 * add new Gateway
 */
add_filter('woocommerce_payment_gateways', 'erc20_add_gateway_class');
function erc20_add_gateway_class($gateways) {
	$gateways[] = 'WC_Token_Gateway';
	return $gateways;
}
/**
 * listen request
 */
add_action('init', 'thankyour_request');
function thankyour_request() {
	/**
	 * get request
	 */
 
	if ($_POST['request']=='request') {
 
		console.log("123123",$_SERVER["REQUEST_URI"]);
	$data = $_POST;

if (is_int((int)$data['orderid'] )  && strlen($data['tx'])==66 && !preg_match('/[^A-Za-z0-9]/', $data['tx']) ) {
	# code...

	
		$order_id = $data['orderid'];
		$tx = $data['tx'];
		if (strlen($tx) != 66 || substr($tx,0,2) != '0x'){
			return ;
		}
		/**
		 * get order
		 */
		$order = wc_get_order($order_id);
		/**
		 * order complete
		 */
		$order->payment_complete();


		/**
		 * transaction hash
		 */
		$order->add_order_note(__("payment completed-", 'woocommerce-ethereum-evm-token-payment-gateway') . " Transaction Hash:"  . $tx );
		/**
		 * exit
		 */
		exit();
}

	}

}
/*
 * class
 */
add_action('plugins_loaded', 'erc20_init_gateway_class');
function erc20_init_gateway_class() {
	/**
	 * 定义 class
	 */
	class WC_Token_Gateway extends WC_Payment_Gateway {

		/**
		 * Class constructor, more about it in Step 3
		 */
		public function __construct() {
			/**
			 *  
			 * @var string
			 */
			$this->id = 'token_payment_gateway';
			/**
			 *  
			 * @var [type]
			 */
			$this->method_title = __('Pay with Token', 'woocommerce-ethereum-evm-token-payment-gateway');
			/**
			 * 
			 */
			$this->order_button_text = __('Use Token Payment', 'woocommerce-ethereum-evm-token-payment-gateway');
			/**
			 * description
			 */
			$this->method_description = __('If you want to use this Payment Gateway, We suggest you read <a href="https://github.com/AlgoNetwork/Token-Payment-Gateway-for-Ethereum-EVM-compatible-Blockchain-ERC20-BEP20-and-WooCommerce">our guide </a> before.', 'woocommerce-ethereum-evm-token-payment-gateway');

			$this->supports = array(
				'products',
			);  

			/**
			 *  
			 */
			$this->init_settings();
			$this->init_form_fields();

			//  
			foreach ($this->settings as $setting_key => $value) {
				$this->$setting_key = $value;
			}

			/**
			 *  
			 */
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
			add_action('woocommerce_api_compete', array($this, 'webhook'));
			add_action('admin_notices', array($this, 'do_ssl_check'));
			add_action('woocommerce_thankyou', array($this, 'thankyou_page'));

		}

		/**
		 * 插件设置项目
		 */
		public function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
					'title' => __('Enable/Disable', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'label' => __('EVM Token Payment Gateway', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'checkbox',
					'default' => 'no',
				),
				'title' => array(
					'title' => __('Title', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'text',
					'description' => __('Title Will Show at Checkout Page', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'default' => 'EVM Token Payment Gateway(ERC20-BEP20)',
					'desc_tip' => true,
				),
				'description' => array(
					'title' => __('Description', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'textarea',
					'description' => __('Description  Will Show at Checkout Page', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'default' => __('Please make sure you already install Metamask and enable it.', 'woocommerce-ethereum-evm-token-payment-gateway'),
				),
				'icon' => array(
					'title' => __('Payment Token icon', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'text',
					'default' => 'https://raw.githubusercontent.com/AlgoNetwork/Free-Woocommerce-Ethereum-Evm-Compatible-ERC20-BEP20-Token-Payment-Gateway/e005d6fdde727d40e413617c2610987af14d9279/assets/token.png',
					'description' => __('Image Height:25px', 'woocommerce-ethereum-evm-token-payment-gateway'),
				),
				'target_address' => array(
					'title' => __('Your Wallet Address', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'text',
					'description' => __('Token Will Transfer into this Wallet', 'woocommerce-ethereum-evm-token-payment-gateway'),
				),
				'abi_array' => array(
					'title' => __('Token Contract ABI', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'textarea',
					'description' => __('input the ABI of token contract', 'woocommerce-ethereum-evm-token-payment-gateway'),
				),

				'contract_address' => array(
					'title' => __('Token Contract Address', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'text',
					'description' => __('type the address of used token.', 'woocommerce-ethereum-evm-token-payment-gateway'),
				),
				'tokenDecimals' => array(
					'title' => __('Token Decimal', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'text',
					'default' => '18',
				),

				'BlockchainNetwork' => array(
					'title' => __('Blockchain Network', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'text',
					'description' => __('Blockchain Network ID,like 1,2,86..Get id from:https://algonetwork.github.io/EVM-Blockchain-Index/', 'woocommerce-ethereum-evm-token-payment-gateway'),
				),
				'BlockchainNetworkName' => array(
					'title' => __('Blockchain Network', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'text',
					'description' => __('Blockchain Name,like:Ethereum,binance,or any other.', 'woocommerce-ethereum-evm-token-payment-gateway'),
				),
				'notice' => array(
					'title' => __('Notice', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'type' => 'textarea',
					'default' => __('You can buy tokens on this website:www.b.com', 'woocommerce-ethereum-evm-token-payment-gateway'),
					'description' => __('Notice', 'woocommerce-ethereum-evm-token-payment-gateway'),
				),
			);
			$this->form_fields += array(

				'ad1' => array(
					'title' => 'Generate Token',
					'type' => 'title',
					'description' => '<a href="https://algonetwork.github.io/TokenFactory/">Generate Token with One-click</a>',
				),
				'ad2' => array(
					'title' => 'audit',
					'type' => 'title',
					'description' => 'audit',
				),
				'ad3' => array(
					'title' => 'Github',
					'type' => 'title',
					'description' => ' <a href="https://github.com/AlgoNetwork/Free-Woocommerce-Ethereum-Evm-Compatible-ERC20-BEP20-Token-Payment-Gateway">Contact</a> ',
				),

			);
		}
		/**
		 *  load js 
		 */
		public function payment_scripts() {
			wp_enqueue_script('token_web3', plugins_url('assets/web3.min.js', __FILE__), array('jquery'), 1.1, true);
			wp_register_script('token_payments', plugins_url('assets/payments.js', __FILE__), array('jquery', 'token_web3'));
			wp_enqueue_script('token_payments');
		}

		/**
		 *  
		 */
		public function validate_fields() {
			return true;
		}

		/**
		 * 
		 */
		public function process_payment($order_id) {
			global $woocommerce;
			$order = wc_get_order($order_id);
			/**
			 *  
			 */
			$order->add_order_note(__('create order ,wait for payment', 'woocommerce-ethereum-evm-token-payment-gateway'));
			/**
			 *  
			 */
			$order->update_status('unpaid', __('Wait For Payment', 'woocommerce-ethereum-evm-token-payment-gateway'));
			/**
			 * reduce stock.
			 */
			$order->reduce_order_stock();
			/**
			 *  
			 */
			WC()->cart->empty_cart();
			/**
			 * 
			 */
			return array(
				'result' => 'success',
				'redirect' => $this->get_return_url($order),
			);
		}
		/**
		 * check ssl 
		 */
		public function do_ssl_check() {
			if ($this->enabled == "yes") {
				if (get_option('woocommerce_force_ssl_checkout') == "no") {
					echo "<div class=\"error\"><p>" . sprintf(__("<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>"), $this->method_title, admin_url('admin.php?page=wc-settings&tab=checkout')) . "</p></div>";
				}
			}
		}
		/**
		 *  
		 *  
		 */
		public function thankyou_page($order_id) {
			/**
			 *  
			 */
			if (!$order_id) {
				return;
			}

			$order = wc_get_order($order_id);
			/**
			 * check order if need payment
			 */
			//$order->needs_processing()
			//$order->needs_payment()
			if ($order->needs_payment()) {
				/**
				 * show pay button if needs payment.
				 */
				echo '<script>var order_id = ' . $order_id . ';'. 'var BlockchainNetwork = "' . $this->BlockchainNetwork . '";'.'var BlockchainNetworkName = "' . $this->BlockchainNetworkName . '";'. 'var tokendecimals = "' . $this->tokenDecimals . '";' . 'var contract_address = "' . (string) $this->contract_address . '";var abiArray = ' . $this->abi_array . '; var target_address = "' . $this->target_address . '"; </script>';
				echo __('<h2 class="h2thanks">Use Metamask Pay this Order</h2>', 'woocommerce-ethereum-evm-token-payment-gateway');
				echo __('Click Button Below, Pay this order.<br>', 'woocommerce-ethereum-evm-token-payment-gateway');
				echo '<div id="UserAccount" ></div><br>';
				echo '<div id="NetworkName"  style="color:red;"></div><br>';
				echo '<span style="margin:5px 0px;">' . 'The blockchains we use is:' .$this->BlockchainNetworkName . "</span><br>";
				echo '<span style="margin:5px 0px;">' . $this->notice . "</span><br>";
				//echo '<span style="margin:5px 0px;">' . $this->tokenDecimals . "</span><br>";
				echo '<div><button onclick="requestPayment(' . (string) $order->get_total() . ')">' . __('Open Metamask', 'woocommerce-ethereum-evm-token-payment-gateway') . '</button></div>';

			} else {
				/**
				 * 
				 */
				echo __('<h2>Your Order is already completed.</h2>', 'woocommerce-ethereum-evm-token-payment-gateway');
			}
		}
	}
}
