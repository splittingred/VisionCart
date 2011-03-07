<?php
/**
 * VisionCart
 * 
 * @package 
 * @author 
 * @copyright 2010
 * @version $Id$
 * @access public
 */
class VisionCart {
	public $router = array();
	public $config = array();
	public $placeholders = array(); //Set private after dev
	public $domPdf = null;
	public $logLevel = 0; // 0 = no logging, 1 = log critical errors, 2 = log notices and errors
	public $devMode = false; 
	
	public $shop;
	public $category;
	public $product;
	
	protected $configFiles = array();
	
    /**
     * Constructs the VisionCart object
     *
     * @param modX &$modx A reference to the modX object
     * @param array $config An array of configuration options
     */
    public function __construct(modX &$modx,array $config=array()) {
        $this->modx =& $modx;
        
        $settings = array();
        $path = array(
			'core' => $this->modx->getOption('visioncart.core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH)),
			'assets' => $this->modx->getOption('visioncart.assets_url', null, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL)),
			'assetsBase' => $this->modx->getOption('visioncart.assets_path', null, $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH))
		);
		
		$query = $this->modx->newQuery('modSystemSetting');
		$query->where(array(
			'key:LIKE' => 'visioncart_%',
			'namespace' => 'visioncart'
		));
		
		$stack = $this->modx->getCollection('modSystemSetting', $query);
		
		foreach($stack as $setting) {
			$settings[end(explode('visioncart_', $setting->get('key')))] = $setting->get('value');
		}
		
		$query = $this->modx->newQuery('modContextSetting');
		$query->where(array(
			'key:LIKE' => 'visioncart_%',
			'namespace' => 'visioncart'
		));
		
		$stack = $this->modx->getCollection('modContextSetting', $query);
		
		foreach($stack as $setting) {
			$settings[end(explode('visioncart_', $setting->get('key')))] = $setting->get('value');
		}

        $this->config = array_merge($settings, array(
            'basePath' => $path['core'].'components/visioncart/',
            'corePath' => $path['core'].'components/visioncart/',
            'modelPath' => $path['core'].'components/visioncart/model/',
            'assetsBasepath' => $path['assetsBase'].'components/visioncart/',
            'processorsPath' => $path['core'].'components/visioncart/processors/',
            'assetsUrl' => $path['assets'].'components/visioncart/',
            'connectorUrl' => $path['assets'].'components/visioncart/connector.php'
        ), $config);

		unset($modx, $path, $settings, $setting, $query, $stack, $shopId, $shop);

		if (isset($this->config['context']) && $this->config['context'] != '') {
			$this->initialize($this->config['context'], array_merge($config, array(
				'initialize' => (isset($this->config['initialize']) && $this->config['initialize'] != '') ? $this->config['initialize'] : 'default'	
			)));
		}
		
		unset($config);
		
		if ($this->modx->context->get('key') == 'mgr') {
			$this->modx->addPackage('visioncart', $this->config['modelPath']);	
		}
		
		if ($this->devMode) {
			$this->writeElements();	
		}
    }
    
    /**
     * writeElements
     * Write snippets and chunks to elements folder
     *
     * @access private
     * @return void
     */
    private function writeElements() {
        // Put all VisionCart snippets into the snippets folder, dev only!
        $snippets = $this->modx->getCollection('modSnippet', array(
			'name:LIKE' => 'vc%'
		));
		
    	foreach($snippets as $snippet) {
			file_put_contents($this->config['basePath'].'elements/snippets/'.$snippet->get('name').'.php', '<?php'."\n".$snippet->get('snippet'));
		}
		
		// Put all VisionCart chunks into the chunk folder, dev only!
		$chunks = $this->modx->getCollection('modChunk', array(
			'name:LIKE' => 'vc%'
		));
		
		foreach($chunks as $chunk) {
			file_put_contents($this->config['basePath'].'elements/chunks/'.$chunk->get('name').'.tpl', $chunk->get('snippet'));
		}	
    }
    
    /**
     * initialize
     * Initialize a specific context or action 
     *
     * @access public
     * @param string $context Default context to launch is 
     * @param array $config Configuration 
     * @return bool
     */
    public function initialize($context='web', $config=array()) {
        switch ($context) {
            case 'mgr':
            	$this->modx->regClientStartupHTMLBlock('<script type="text/javascript">var siteId = \''.$this->modx->site_id.'\';</script>');
                $this->modx->lexicon->load('visioncart:default');

                if (!$this->modx->loadClass('visioncart.request.visionCartControllerRequest', $this->config['modelPath'], true, true)) {
                    return 'Could not load controller request handler.';
                }
                
                $this->request = new visionCartControllerRequest($this);
                return $this->request->handleRequest();
            	break;
            case 'connector':
                if (!$this->modx->loadClass('visioncart.request.visioncartConnectorRequest', $this->config['modelPath'], true, true)) {
                    echo 'Could not load connector request handler.'; die();
                }
                $this->request = new visioncartConnectorRequest($this);
                return $this->request->handle();
            	break;
           	case 'ajax':
           		break;
           	default: // Context default
           		if (!empty($this->router)) {
           			return true;
           		}
           		
           		$config['requestURL'] = $this->modx->getOption('requestURL', $config, $_SERVER['REQUEST_URI']);
           		$config['method'] = $this->modx->getOption('method', $config, 'resource');
           		$config['requestURL'] = str_replace($this->modx->getOption('site_url'), '', $config['requestURL']);
           		
           		// Remove subdir
           		$baseUrl = $this->modx->getOption('base_url');
           		if (substr($config['requestURL'], 0, strlen($baseUrl)) == $baseUrl) {
           			$config['requestURL'] = substr($config['requestURL'], strlen($baseUrl));
           		}
           		
           		$config['event'] = $this->modx->getOption('event', $config, false);
           		
           		if ($config['method'] == 'ajax') {
           			$this->modx->addPackage('visioncart', $this->config['modelPath']);	
           		}

                $router = array();
				$url = explode('/', $config['requestURL']);
                
                // Remove empty values, reset internal counter to 0 and return the sanitized array
                $url = array_values(array_filter($url, create_function('$value', 'if (trim($value) != \'\') { return true; }')));
                $url = array_map(create_function('$value', 'return trim($value);'), $url);
                                
				if (!empty($url)) {
					$router['shop'] = (string) $url[0];
					
					if (isset($router['shop']) && $router['shop'] != '') {
						$this->shop = $this->getShop(array(
							'type' => 'alias',
							'value' => $router['shop']
						));
					}
					
					list($dest, $query) = explode('?', end($url));
	                if (substr($dest, 0, 1) != '?' && substr($dest, -5) == '.html') {
	                	$dest = substr($dest, 0, -5);
	                	
	                	$id = end(explode('-', $dest));
	                	if (isset($id) && $id != false && is_numeric($id) && $id != 0) {
	                		$router['product'] = array(
	                			'id' => (int) $id,
								'alias' => (string) $dest 
							);
							
							// AJAX
							if ($config['method'] == 'ajax') {
								$this->product = $this->getProduct($router['product']['id']);
							}
	                	} else {
	                		// Set a temporary placeholder for the last category
	                		$router['category'] = (string) $dest;
	                	}
	                	
	                	array_pop($url);
	                }
	                
	                array_shift($url);
                
	                if (!empty($url)) {
	                    $router['categories'] = array();
	                    
	                    foreach($url as $part) {
	                    	if (substr($part, -5) == '.html') {
	                    		$part = substr($part, 0, -5);
	                    	}
	                    	
	                    	if (substr($part, 0, 1) != '?') {
	                        	$router['categories'][] = $part;
	                    	}
	                    }
	                }
	                
	                // If a temporary category has been set, set it as the last category in the array and unset
	                if (isset($router['category']) && $router['category'] != '') {
	                	$router['categories'][] = $router['category'];
	                	unset($router['category']);
	                }
	                
	                // AJAX
					if ($config['method'] == 'ajax') {
						$this->category = $this->getCategory((string) end($router['categories']), array(
							'whereColumn' => 'alias'
						));
					}
					
	                $router['query'] = array_merge($_GET, $_REQUEST);
					$this->router = $router;
	                unset($router, $shop);
				}
				
				if (!is_object($this->shop) && isset($this->config['default_shop']) && is_numeric($this->config['default_shop'])) {
					$this->shop = $this->getShop(array(
						'type' => 'id',
					  	'value' => (int) $this->config['default_shop']
					));
					
					if ($this->shop != null) {
						$this->router['shop'] = (string) $this->shop->get('alias');
					} else {
						// If no default shop is set, just get the first from the database (this is not what we want! System setting MUST exist!)
						$query = $this->modx->newQuery('vcShop');
						$query->where(array('alias:!=' => ''));
						$query->limit(1);
						$this->shop = $this->modx->getObject('vcShop', $query);

						if ($this->shop != null) {
							$this->router['shop'] = (string) $this->shop->get('alias');
						}
					}
				}
            	break;
        }
        
        return true;
    }
    
    /**
     * route
     * Route the url based parameters through 
     *
     * @access public
     * @param array $config Configuration 
     * @return void
     */
    public function route($config=array()) {
		if (isset($this->router['product']['id']) && is_numeric($this->router['product']['id']) && $this->router['product']['id'] != 0) {
			$this->category = $this->getCategory((string) end($this->router['categories']), array(
				'whereColumn' => 'alias'
			));
			
			$this->product = $this->getProduct($this->router['product']['id']);
			
			if ($this->product != null) {
				$this->product->set('url', $this->makeUrl(array(
					'productCategory' => (int) $this->product->getOne('ProductCategory')->get('id'),
					'shopId' => (int) $this->shop->get('id')
				)));
				
				if ($config['method'] == 'resource') {
					$id = (int) (is_object($this->modx->resource)) ? $this->modx->resource->get('id') : 0;
					
					if (isset($this->product) && is_object($this->product) && $id != (int) $this->getShopSetting('productResource', $this->shop->get('id'))) {
						$this->modx->sendForward((int) $this->getShopSetting('productResource', $this->shop->get('id')));
					}
				}
			}
		}
		
		if (isset($this->router['categories']) && !empty($this->router['categories'])) {
			$this->category = $this->getCategory((string) end($this->router['categories']), array(
				'whereColumn' => 'alias'
			));
			
			if ($config['method'] == 'resource') {
				$id = (int) (is_object($this->modx->resource)) ? $this->modx->resource->get('id') : 0;
				
				if ($this->category != null) {
					if ($id != $this->getShopSetting('categoryResource', $this->shop->get('id'))) {
						$categoryConfig = $this->category->get('config');
						if (isset($categoryConfig['resource']) && $categoryConfig['resource'] != 0) {
							$this->modx->sendForward($categoryConfig['resource']);	
						} else {
							$this->modx->sendForward($this->getShopSetting('categoryResource', $this->shop->get('id')));	
						}
					}
				}
				
				if (isset($this->category) && is_object($this->category) && $id != (int) $this->getShopSetting('categoryResource', $this->shop->get('id'))) {
					$categoryConfig = $this->category->get('config');
					
					if (isset($categoryConfig['resource']) && $categoryConfig['resource'] != 0) {
						$this->modx->sendForward($categoryConfig['resource']);
					} else {
						$this->modx->sendForward((int) $this->getShopSetting('categoryResource', $this->shop->get('id')));
					}
				}
			}
		}
		
		if ($config['method'] == 'resource') {
			$id = (int) (is_object($this->modx->resource)) ? $this->modx->resource->get('id') : 0;
			
			if ($id != $this->getShopSetting('shopResource', $this->shop->get('id'))) {
				$this->modx->sendForward($this->getShopSetting('shopResource', $this->shop->get('id')));
			}
		}
    }
    
    /**
     * assign
     * Automaticly assigns OnLoadWebDocument placeholders
     *
     * @access public
     * @param array $config Configuration 
     * @return void
     */
    public function assign($config=array()) {
    	if ($this->shop == null) {
    		return false;
    	}
    	
    	$this->modx->toPlaceholders(array(
    		'shop' => $this->shop->toArray()
		), 'vc');
		
		if ($this->category != null) {
			$this->modx->toPlaceholders(array(
				'category' => $this->category->toArray()
			), 'vc');
		}
		
		if ($this->product != null) {
			$this->modx->toPlaceholders(array(
				'product' => $this->product->toArray()
			), 'vc');
		}
    }
    
    /**
     * getConfigFile
     * Fetch the filed configuration from the themes folder, depending on the configuration 
     *
     * @access public
     * @param int $shopId The shopId under which to cache the fetched config
	 * @param string $topic The function/snippet name in which it will be used
	 * @param key Optional. The key inside the configuration array (direct fetch)
	 * @param array $config Configuration 
     * @return array The fetched or empty array
     */
    public function getConfigFile($shopId, $topic, $key=null, $config=array()) {
    	$config['config'] = $this->modx->getOption('config', $config, 'default');
    	$config['context'] = $this->modx->getOption('context', $config, 'web');
    	
    	if (!isset($this->configFiles[$shopId][$topic][$config['config']])) {
    		$basePath = $this->config['assetsBasepath'];
    		$theme = $this->getShopSetting('shopTheme', $shopId);
    		
   			if (!isset($config['theme'])) {
   				if ($theme != null && $theme != '') {
   					$config['theme'] = (string) $theme;
				}
   			}
			
			$file = $basePath.$config['context'].'/themes/'.$config['theme'].'/'.$topic.'/config.'.$config['config'].'.php';
			
			if (is_file($file)) {
				require_once($file);
				
				if (!isset($params)) {
					$params = array();
				}
				
				$this->configFiles[$shopId][$topic][$config['config']] = $params;
			}
    	}
    	
    	if (isset($this->configFiles[$shopId][$topic][$config['config']])) {
    		if (!isset($key)) {
    			return $this->configFiles[$shopId][$topic][$config['config']];
    		}
    		
    		if (isset($this->configFiles[$shopId][$topic][$config['config']][$key])) {
    			return $this->configFiles[$shopId][$topic][$config['config']][$key];
    		}
    	}
    	
    	return array();
    }
    
    /**
     * Get the shop by Id
     *
     * @access public
     * @param array $config The configuration array needed to fetch a shop by criteria
     * @return string The value of the config key
     */
    public function getShop($config=array()) {
    	$config['type'] = $this->modx->getOption('type', $config, 'id');
    	$config['value'] = $this->modx->getOption('value', $config, false);
    	$config['asArray'] = $this->modx->getOption('asArray', $config, false);
    	$config['hideInactive'] = $this->modx->getOption('hideInactive', $config, true);
    	
    	if ($config['value'] == false) {
    		if (!isset($this->router['shop'])) {
    			return false;
    		}
    		
    		$config['type'] = 'alias';
    		$config['value'] = $this->router['shop'];
    	}
    	
    	$shop = $this->modx->getObject('vcShop', array(
			$config['type'] => $config['value'],
			'active' => ($config['hideInactive'] != false) ? 1 : 0
		));
		
    	if ($shop == null) {
    		return false;
    	}
    	
    	if ($config['asArray'] == true) {
    		return $shop->toArray();
    	}
    	
    	return $shop;
    }
    
    /**
     * Gets a config variable belonging to a shop
     *
     * @access public
     * @param string $key The key of the config variable
     * @param int $shopId The ID of the shop
     * @return string The value of the config key
     */
    public function getShopSetting($key, $shopId=null) {
    	if (!isset($shopId)) {
    		$shop = $this->shop;	
    	} else {
    		$shop = $this->modx->getObject('vcShop', $shopId);
    	}
    	
    	if ($shop == null) {
    		return false;	
    	}
    	
    	$config = $shop->get('config');
    	
    	if (isset($config[$key])) {
    		return $config[$key];	
    	}
    	
    	return false;
    }
    
    /**
     * Set a cookie
     *
     * @access public
     * @param string $key
     * @param string $value
     * @param int $lifeTime
     * @return void
     */
    public function setCookie($key, $value, $lifeTime=86400) {
    	setcookie($key, $value, time()+$lifeTime, '/', '.'.$_SERVER['HTTP_HOST']);
    }
   
    /**
     * Set a cookie
     *
     * @access public
     * @param string $key
     * @return string Cookie value or NULL
     */
    public function getCookie($key) {
    	if (isset($_COOKIE[$key])) {
    		return $_COOKIE[$key];	
    	}
    	
    	return null;
    }
    
     /**
     * Delete a cookie
     *
     * @access public
     * @param string $key
     * @param string $value
     * @param int $lifeTime
     * @return void
     */
    public function deleteCookie($key) {
    	setcookie($key, '', time()-5, '/', '.'.$_SERVER['HTTP_HOST']);
    }
    
    /**
     * Get the current basket and create one if neccesary
     *
     * @access public
     * @param bool $createBasket
     * @return object The basket xPDO object
     */
    public function getBasket($createBasket=true) {
    	$basketName = 'vc_basket_'.$this->shop->get('id');

    	// Check if the cookie is set
		if (isset($_COOKIE[$basketName])) {
			// Check if there's a basket in the database belonging to this unique ID
			$basket = $this->modx->getObject('vcOrder', array(
				'basketid' => $_COOKIE[$basketName]
			));
			
			if ($basket == null || (($basket->get('userid') != 0) && ($basket->get('userid') != $this->modx->user->get('id')))) {
				$this->deleteCookie($basketName);
			} else {  
				// Only return it if the basket is new
				if ($basket->get('status') == -1) {
					if (($this->modx->user->get('id') == $basket->get('userid') || $basket->get('userid') == 0) || $this->modx->context->get('key') == 'mgr') {
						return $basket;	
					}
				} else {
					$this->deleteCookie($basketName);
				}
			}
		} 
		
		if ($createBasket) {
			// If this is where we are, there's no basket yet so let's create one
			$id = $this->uniqueId();
	
			$basket = $this->modx->newObject('vcOrder', array(
				'shopid' => $this->shop->get('id'),
				'userid' => 0,
				'basketid' => $id,
				'status'   => -1
			));
			
			$this->setCookie($basketName, $id, 3600*48);
			$basket->save();
			
			return $basket;
		}
		
		return null;
    }
    
    public function sendStatusEmail($order, $sendOrderPdf=false, $status=null) {
    	if (is_array($order)) {
    		$order = $this->modx->getObject('vcOrder', $order['id']);	
    	}
    	
    	if (!isset($status) || $status == null) {
    		$status = $order->get('status');	
    	}
    	
    	if ($sendOrderPdf) {
			// Send an order email
			$tempFile = tempnam(sys_get_temp_dir(), 'pdf');
			$this->orderToPdf($order, array(
				'outputTo' => 'file',
				'fileName' => $tempFile
			)); 

			$orderPdf = array(
				'path' => $tempFile,
				'name' => $order->get('ordernumber').'.pdf',
				'type' => 'application/pdf'
			);
    	}
    	
		// Check what chunk to use
		switch($order->get('status')) {
			default:
			case 1:
				// Any other status (new order)
				$innerChunk = 'vcEmailStatusNew';
				break;
			case 2:
				// Any other status (new order)
				$innerChunk = 'vcEmailStatusConfirmed';
				break;
			case 3:
				// Paid
				$innerChunk = 'vcEmailStatusPaid'; 
				break;
			case 4:
				// Shipped
				$innerChunk = 'vcEmailStatusShipped';
				break;	
		}
		
		$userData = $order->get('userdata');
		$user = $this->modx->getObject('modUser', $order->get('userid'));
		$profile = $user->getOne('Profile');
		
		$chunkProperties = $order->toArray(); 
		$chunkProperties['shop'] = $order->getOne('Shop')->toArray();
		$chunkProperties['innerChunk'] = $this->modx->getChunk($innerChunk, $chunkProperties);
		
		$orderArray = $order->toArray();
		$search = array();
		$replacements = array();
		
		foreach($orderArray as $key => $value) {
			$search[] = '[[+'.$key.']]';
			$replacements[] = $value;	 
		}
		
	    // Send mail with PDF
		$status = $this->modx->visioncart->sendMail(array(
			'shopid' => $order->get('shopid'), 
			'subject' => str_replace($search, $replacements, $this->getShopSetting('emailSubjectStatusUpdate', $order->get('shopid'))), 
			'chunk' => 'vcEmailOuter', 
			'placeholders' => $chunkProperties, 
			'to' => $profile->get('email'),
			'attachments' => array($orderPdf)
		));		
		
		if (isset($tempFile)) {
			@unlink($tempFile);
		}
		
		return $status;
    }
    
    /**
     * Unlink the cookie from the order and delete it
     *
     * @access public
     * @return void
     */
    public function clearBasket() {
    	$basketName = 'vc_basket_'.$this->shop->get('id');
    	
    	// Check if the cookie is set
		if (isset($_COOKIE[$basketName])) {
			// Check if there's a basket in the database belonging to this unique ID
			$basket = $this->modx->getObject('vcOrder', array(
				'basketid' => $_COOKIE[$basketName]
			));
			
			$this->deleteCookie($basketName);
			if ($basket != null && $basket->get('userid') == $this->modx->user->get('id')) {
				// Remove the basket if status == -1 (an actual basket and not an order)
				if ($basket->get('status') == -1 && $basket->get('ordernumber') == '') {
					$basket->remove();
				}
			}
		}
		
		return true;
    }
    
    public function calculateTaxes($order) {
    	if (is_array($order)) {
    		$order = $this->modx->getObject('vcOrder', array(
    			'id' => $order['id']
    		));	
    	}

    	$basket = $order->get('basket');
    	
    	$taxes = array();
    	$taxAmounts = array();
    	$highestTax = null;
    	foreach($basket as $product) {
    		$productObject = $this->modx->getObject('vcProduct', $product['id']);	
    		
    		if ($productObject != null) {
    			$taxCategory = $productObject->getOne('TaxCategory');
    			
    			if ($taxCategory != null) { 
    				// Check for highest tax
    				if ($highestTax == null) {
    					$highestTax = $taxCategory;	
    				} elseif ($highestTax->get('pricechange') < $taxCategory->get('pricechange')) {
						$highestTax = $taxCategory;	
    				}
    				
    				// Add tax to master array
    				if (!isset($taxes[$taxCategory->get('id')])) {
    					$taxes[$taxCategory->get('id')] = $taxCategory->toArray();	
    				}
    				
    				// Calculate taxes for this product
	    			$tax = $taxCategory->get('pricechange');
	    			if (!isset($taxAmounts[$taxCategory->get('id')])) {
	    				$taxAmounts[$taxCategory->get('id')] = 0;	
	    			}
	    			
	    			$productPrice = $this->calculateProductPrice($product, true);
	    			
	    			$taxAmounts[$taxCategory->get('id')] += ((($productPrice['ex'] / 100) * $taxCategory->get('pricechange')) * (int) $product['quantity']);
    			}
    		}
    	}
    	
    	// Get shipping taxes
    	$shippingTaxes = 0;
    	if ($this->getShopSetting('calculateShippingTaxes', $order->get('shopid')) == 1) {
    		$shippingTaxes = $order->get('shippingcostsin') - $order->get('shippingcostsex');
    	}
    	
    	// Get payment taxes
    	$paymentTaxes = 0;
    	if ($this->getShopSetting('calculatePaymentTaxes', $order->get('shopid')) == 1) {
    		$paymentTaxes = $order->get('paymentcostsin') - $order->get('paymentcostsex');
    	}

    	// Set taxes in master array
    	foreach($taxAmounts as $key => $value) {
    		if ($taxes[$key]['pricechange'] == $highestTax->get('pricechange')) {
    			$value = $value + $shippingTaxes + $paymentTaxes;	
    		}
    		
    		$taxAmount = number_format($value, 2, '.', '');
    		$taxes[$key]['amount'] = $taxAmounts;	
    		$taxes[$key]['display']['amount'] = $this->money($taxAmount, array('shopId' => $order->get('shopid')));
    		$taxes[$key]['display']['percentage'] = $taxes[$key]['pricechange'].'%';
    	}

    	unset($taxAmounts, $basket);
   	
    	return $taxes;
    }
    
    public function getOrders($config = array()) {
    	$config['status'] = $this->modx->getOption('status', $config, 1);
    	$config['operand'] = $this->modx->getOption('operand', $config, '>=');
    	$config['shopId'] = $this->modx->getOption('shopId', $config, 0);
    	$config['userId'] = $this->modx->getOption('userId', $config, 0);
    	
    	if ($config['shopId'] == 0 && isset($this->shop) && is_object($this->shop)) {
    		$config['shopId'] = $this->shop->get('id');
    	}
    	
    	if ($config['userId'] == 0 && $this->modx->user->isAuthenticated()) {
    		$config['userId'] = $this->modx->user->get('id');
    	}
    	
    	$config = array_merge($this->getConfigFile(31, 'orderView'), $config);
    	
    	$query = $this->modx->newQuery('vcOrder');
    	$query->where(array(
			'shopid' => $config['shopId'],
			'userid' => $config['userId'],
			'status:'.$config['operand'] => $config['status']
		));
		$query->sortby('ordertime', 'DESC');
		
		$orders = array();
	    $stack = $this->modx->getCollection('vcOrder', $query);
	    
	    foreach($stack as $order) {
	    	$order->set('products', count($order->get('basket')));
	    	$orders[] = $order;
	    }
	    
	    unset($config, $query, $stack, $order);
	    
	    return $orders;
    }
    
    public function getOrder($orderNumber = 0, $config = array()) {
    	if ($orderNumber == 0 && isset($this->router['query']['id'])) {
    		$orderNumber = $this->router['query']['id'];
    	} else {
    		return false;
    	}
    	
		$config['shopId'] = $this->modx->getOption('shopId', $config, 0);
    	$config['userId'] = $this->modx->getOption('userId', $config, 0);
    	
    	if ($config['shopId'] == 0 && isset($this->shop) && is_object($this->shop)) {
    		$config['shopId'] = $this->shop->get('id');
    	}
    	
    	if ($config['userId'] == 0 && $this->modx->user->isAuthenticated()) {
    		$config['userId'] = $this->modx->user->get('id');
    	}
    	
		$order = $this->modx->getObject('vcOrder', array(
    		'ordernumber' => $orderNumber,
			'shopid' => $config['shopId'],
			'userid' => $config['userId']
		));
		
		unset($orderNumber, $config);
		
		return $order;
    }
    
    public function uniqueId() {
    	$uniqueId = sha1(uniqid().mt_rand(0, 9999999999).$_SERVER['REMOTE_ADDR']);
    	
    	return $uniqueId;
    }
    
    /**
     * Format money string to webshop's settings
     *
     * @access public
     * @param string $amount Amount of money
     * @return string The formatted money
     */
    public function money($amount, $config=array()) {
    	$amount = floatval($amount);
    	$config['shopId'] = $this->modx->getOption('shopId', $config, 0);

    	if ($config['shopId'] == 0) {
    		$shop = $this->shop->toArray();
    	} else {
    		$shop = $this->modx->getObject('vcShop', $config['shopId']);
    		$shop = $shop->toArray();	
    	}
		
    	$config['currency'] = $this->modx->getOption('currency', $config, $shop['config']['currency']);
		    	
    	if (!isset($config['format'])) {
	    	$config['decimalSeparator'] = $this->modx->getOption('decimalSeparator', $shop['config'], ',');
	    	$config['thousandsSeparator'] = $this->modx->getOption('thousandsSeparator', $shop['config'], '');
	    	if ($config['currency'] != '') {
    			$money = $config['currency'].' '.number_format($amount, 2, $config['decimalSeparator'], $config['thousandsSeparator']); 
	    	} else {
	    		$money = number_format($amount, 2, $config['decimalSeparator'], $config['thousandsSeparator']);	
	    	}
    	} else {
    		$amount = explode('.', (string) number_format($amount, 2, '.', ''));
    		$money = str_replace('{currency}', $config['currency'], $config['format']);
    		$money = str_replace('{amount}', $amount[0], $money);
    		$money = str_replace('{cents}', $amount[1], $money);
    	}

		return $money;	
    }
    
    /**
     * Get the shop by Id
     *
     * @access public
     * @param array $config The configuration array needed to fetch a shop by criteria
     * @return string The value of the config key
     */
    public function writePdf($html, $config=array()) {
    	$fileName = $this->modx->getOption('fileName', $config, 'output.pdf');
    	$outputTo = $this->modx->getOption('outputTo', $config, 'header');
    	$stylesheet = $this->modx->getOption('stylesheet', $config, array());
    	$inlineStyles = $this->modx->getOption('inlineStyles', $config, '');
    	
    	if (!isset($this->domPdf)) {
    		// Include the class
    		include($this->config['modelPath'].'dompdf/dompdf_config.inc.php');
    		
    		$this->domPdf = new DOMPDF();
    	}	
		
    	$html = str_replace('</head>', '<meta http-equiv="content-type" content="text/html; charset=UTF-8" /></head>', $html);

    	if (is_array($stylesheet)) {
    		foreach($stylesheet as $sheet) {
    			if (is_file($sheet)) {
    				$html = str_replace('</head>', '<style>'.file_get_contents($sheet).'</style></head>', $html);		
    			}
    		}
    	} elseif ($stylesheet != '') {
    		if (is_file($stylesheet)) {
    			$html = str_replace('</head>', '<style>'.file_get_contents($stylesheet).'</style></head>', $html);	
    		}
    	}
    	
    	if ($inlineStyles != '') {
    		$html = str_replace('</head>', '<style>'.$inlineStyles.'</style></head>', $html);	
    	}
    	
    	$this->domPdf->load_html($html);
    	$this->domPdf->render();
    	
    	switch($outputTo) {
    		case 'header':
    			$this->domPdf->stream($fileName);	
    			break;
    		case 'file':
    			$output = $this->domPdf->output();
    		
    			file_put_contents($fileName, $output);
    			break;
    		case 'string':
    			$output = $this->domPdf->output();
    			
    			return $output;
    			break;	
    	}
    	
    	return true;
    }
    
    public function orderToPdf($order, $config=array()) {
    	if (is_array($order)) {
    		$this->modx->getObject('vcOrder', $order['id']);	
    	}
    	
    	$vc =& $this;
		
    	$productRowTpl = $this->modx->getOption('productRowTpl', $config, $this->getConfigFile($order->get('shopid'), 'orderPdf', 'productRowTpl'));
    	$taxRowTpl = $this->modx->getOption('taxRowTpl', $config, $this->getConfigFile($order->get('shopid'), 'orderPdf', 'taxRowTpl'));
    	$orderWrapper = $this->modx->getOption('orderWrapper', $config, $this->getConfigFile($order->get('shopid'), 'orderPdf', 'orderWrapper'));
    	$inlineStyles = $this->modx->getOption('inlineStyles', $config, $this->getConfigFile($order->get('shopid'), 'orderPdf', 'inlineStyles'));
    	
    	$shop = $this->modx->getObject('vcShop', $order->get('shopid'));
    	
    	if ($this->shop == null) {
    		$this->shop = $shop;	
    	}
    	
		$placeHolders['shop'] = $shop->toArray();
		$rawOrder = $order->toArray('', true);
		$placeHolders['order'] = $order->toArray();
		$placeHolders['order']['ordertime'] = $rawOrder['ordertime'];
		$placeHolders['assetsRoot'] = $modx->visioncart->config['assetsUrl'];
		
		// Get the user
		$user = $this->modx->getObject('modUser', $order->get('userid'));
		if ($user != null) {
			$profile = $user->getOne('Profile');
			$extended = $profile->get('extended');
			$placeHolders['userdata']['profile'] = $profile;
		}
		
		$highestTax = $this->getOrderHighestTax($order);
		
		// Get shipping module
		$shippingModule = $order->getOne('ShippingModule');
		if ($shippingModule != null) {
			$returnValue = array();	
			if ($shippingModule->get('controller') != '') {
				$controller = $this->config['corePath'].'modules/shipping/'.$shippingModule->get('controller');
				if (is_file($controller)) {	
					$vcAction = 'getParams';
					
					$returnValue = include($controller);	
					
					if (!is_array($returnValue)) {
						$returnValue = array();	
					}
				
					if (isset($module)) {
						unset($module);	
					}
				}
			}
			$shippingModule = $shippingModule->toArray();
			$placeHolders['shippingModule'] = array_merge($shippingModule, $returnValue);	
		}
		
		// Get payment module
		$paymentModule = $order->getOne('PaymentModule');
		if ($paymentModule != null) {
			$returnValue = array();	
			if ($paymentModule->get('controller') != '') {
				$controller = $this->config['corePath'].'modules/payment/'.$paymentModule->get('controller');
				if (is_file($controller)) {	
					$vcAction = 'getParams';
					
					$returnValue = include($controller);	
					
					if (!is_array($returnValue)) {
						$returnValue = array();	
					}
				
					if (isset($module)) {
						unset($module);	
					}
				}
			}
			$paymentModule = $paymentModule->toArray();
			$placeHolders['paymentModule'] = array_merge($paymentModule, $returnValue);
		}
		
		// Loop through the order basket
		$productContent = '';
		$basket = $order->get('basket');
		if (is_array($basket)) {
			foreach($basket as $product) {
				$productObject = $this->modx->getObject('vcProduct', $product['id']);
				$taxCategory = $productObject->getOne('TaxCategory');
				$productPrice = $this->calculateProductPrice($product, true);
				
				$subtotal = array(
					'ex' => $productPrice['ex'] * $product['quantity'],
					'in' => $productPrice['in'] * $product['quantity']
				);
				
				$productContent .= $this->parseChunk($productRowTpl, array_merge($placeHolders, array(
					'tax' => $taxCategory->toArray(),
					'product' => array_merge($product, array('display' => array(
						'price' => $productPrice,
						'subtotal' => $subtotal
					)))
				)), array('isChunk' => true));
			}
		}
		
		// Loop through taxes
		$taxes = $this->calculateTaxes($order);
		$taxContent = '';
		if (is_array($taxes)) {
			foreach($taxes as $tax) {
				$taxContent .= $this->parseChunk($taxRowTpl, array_merge($placeHolders, array(
					'tax' => $tax,
				)), array('isChunk' => true));
			}
		}
		
		$content = $this->parseChunk($orderWrapper, array_merge($placeHolders, array(
			'products' => $productContent,
			'taxes' => $taxContent
		)), array('isChunk' => true)); 

		$pdfWrapper = '<html><head></head><body>'.$content.'</body></html>';

		echo $this->writePdf($pdfWrapper, array_merge(array(
			'inlineStyles' => $inlineStyles,
			'stylesheet' => '../assets/components/visioncart/mgr/css/style.css',
			'outputTo' => 'header'
		), $config));
		    		
    }
    
    public function getOrderHighestTax($order) {
    	if (is_array($order)) {
    		$order = $this->modx->getObject('vcOrder', $order['id']);	
    	}
    	
    	// Get highest tax
		$orderBasket = $order->get('basket');
		$highestTax = null;
		if (is_array($orderBasket) && sizeof($orderBasket) > 0) {
			foreach($orderBasket as $product) {
				$productObject = $this->modx->getObject('vcProduct', $product['id']);
			    		
				if ($productObject == null) {
					continue;	
				}
				
				// Get the tax category
				$taxCategory = $productObject->getOne('TaxCategory'); 
				
				if ($highestTax == null) {
					$highestTax = $taxCategory;	
				} elseif ($highestTax->get('pricechange') < $taxCategory->get('pricechange')) {
					$highestTax = $taxCategory;	
				}
			}
		}
		
		return $highestTax;
    }
    
    /**
     * Get all data from a single category
     *
     * @access public
     * @param int $id The category id
     * @param array $config
     * @return array Category
     */
    public function getCategory($handle, $config=array()) {
    	$config['whereColumn'] = $this->modx->getOption('whereColumn', $config, 'id');
    	$config['asArray'] = $this->modx->getOption('asArray', $config, false);
    	$config['queryAnd'] = $this->modx->getOption('queryAnd', $config, array());
    	
    	$query = $this->modx->newQuery('vcCategory');
    	$query->where(array(
			$config['whereColumn'] => $handle,
			'active:!=' => 2
		));
		
		if (!empty($config['queryAnd'])) {
			$query->andCondition($config['queryAnd']);
		}
    	
        $category = $this->modx->getObject('vcCategory', $query);
        
        if ($category != null) {
        	if ($config['asArray'] == true) {
        		return $category->toArray();
        	} else {
        		return $category;
        	}
        }
        
        return false;
    }
    
    /**
     * Get all parent categories in a flattened list
     *
     * @access public
     * @param string $parent The ID of the parent (defaults to 0)
     * @param string $config Config
     * @return array The categories
     */
    private function _categoryUltimateParent($config=array()) {
    	$array = array();
    	
    	$config['parent'] = $this->modx->getOption('parent', $config, 0);
    	$config['queryAnd'] = $this->modx->getOption('queryAnd', $config, array());
    	$config['asArray'] = $this->modx->getOption('asArray', $config, false);
    	$config['shopId'] = $this->modx->getOption('shopId', $config, 0);
    	
    	if ($config['shopId'] == 0) {
    		$shop = $this->getShop();
    		
    		if ($shop == null) {
    			return false;
    		}
    		
    		$config['shopId'] = $shop->get('id');
    		unset($shop);
    	}
    	
    	$query = $this->modx->newQuery('vcCategory');
    	$query->where(array(
			'id' => $config['parent']
		));
		
		if (!empty($config['queryAnd'])) {
			$query->andCondition($config['queryAnd']);
		}
		
		$category = $this->modx->getObject('vcCategory', $query);
    	
    	if ($category != null) {
    		if ($config['asArray'] == true) {
 				$array[] = $category->toArray();
    		} else {
    			$array[] = $category;
   			}
   			
   			if ($category->get('parent') != 0) {
				$config['parent'] = $category->get('parent');
				$array = array_merge($array, $this->_categoryUltimateParent($config));
			}
    	}
        
        return $array;
    }
    
    /**
     * Get a single product by Id, specify config as needed
     *
     * @access public
     * @param int $id Product Id
     * @param array $config The configuration array
     * @return array The product
     */
    public function getProduct($id, $config=array()) {
    	$config['queryAnd'] = $this->modx->getOption('queryAnd', $config, array());
    	$config['queryOr'] = $this->modx->getOption('queryOr', $config, array());
    	$config['hideInactive'] = $this->modx->getOption('hideInactive', $config, true);
    	
    	$query = $this->modx->newQuery('vcProduct');
    	$query->where(array(
			'id' => $id
		));
   	
    	if (!empty($config['queryAnd'])) {
    		$query->andCondition($config['queryAnd']);
    	}
    	
    	if (!empty($config['queryOr'])) {
    		$query->orCondition($config['queryOr']);
    	}
    	
    	$product = $this->modx->getObject('vcProduct', $query);
    	
    	
    	if ($product == null) {
    		return false;
    	}
    	
		if ($config['hideInactive'] == true) {
			if (!$this->isPublished($product)) {
				return false;
			}
		}
    	
    	$product->set('display', array('price' => $this->calculateProductPrice($product, true)));
    	
    	if ($config['asArray'] == true) {
    		return $product->toArray();
    	}
    	
    	return $product;
   	}
    
    /**
     * Get all the products within a category
     *
     * @access public
     * @param int $categoryId The category id
     * @param array $config The configuration array
     * @return array The products
     */
    public function getProducts($categoryId, $config=array()) {
    	$products = array();

    	$config['asArray'] = $this->modx->getOption('asArray', $config, false);
    	$config['parent'] = $this->modx->getOption('parent', $config, 0);
    	$config['hideInactive'] = $this->modx->getOption('hideInactive', $config, true);
    	$config['hideSKU'] = $this->modx->getOption('hideSKU', $config, true);
    	$config['sortBy'] = $this->modx->getOption('sortBy', $config, 'ProductCategory.sort');
    	$config['sort'] = $this->modx->getOption('sort', $config, 'ASC');
    	$config['shopId'] = $this->modx->getOption('shopId', $config, 0);
    	$config['limit'] = $this->modx->getOption('limit', $config, 0);
    	$config['offset'] = $this->modx->getOption('offset', $config, 0);

    	if ($config['shopId'] == 0) {
    		$shop = $this->getShop();
    		
    		if ($shop == null) {
    			return false;
    		}
    		
    		$config['shopId'] = $shop->get('id');
    		unset($shop);
    	}
    	
        $query = $this->modx->newQuery('vcProduct');
		$query->innerJoin('vcProductCategory', 'ProductCategory', 'ProductCategory.productid = vcProduct.id');
		$query->select(array(
			'ProductCategory.*',
			'vcProduct.*',
			'ProductCategory.id AS linkId'
		));
		
		$where = array(
			'ProductCategory.categoryid' => $categoryId,
			'ProductCategory.shopid' => $config['shopId'],
			'vcProduct.shopid' => $config['shopId'],
			'vcProduct.active:!=' => 2
		);
		
		if ($config['hideSKU'] == true && $config['parent'] == 0) {
			$where['vcProduct.parent'] = 0;
		} elseif ($config['parent'] != 0) {
			$where['vcProduct.parent'] = $config['parent'];
		}
		
		$query->where($where, xPDOQuery::SQL_AND);
		
		if (isset($config['queryAnd']) && !empty($config['queryAnd'])) {
			$query->where($config['queryAnd']);
		}
		
		$count = $this->modx->getCount('vcProduct', $query);
				
		if ($config['limit'] != 0) {
			$query->limit($config['limit'], $config['offset']);
		}
		
		$query->sortby($config['sortBy'], $config['sort']);
		
		$collection = $this->modx->getCollection('vcProduct', $query);
		
		foreach($collection as $product) {
			if (!$this->isPublished($product)) {
				continue;	
			}
			
			// Calculate discounts
			$productPrice = $this->calculateProductPrice($product, true);
			$product->set('display', array(
				'price' => array(
					'in' => $productPrice['in'],
					'ex' => $productPrice['ex']
				)
			));
			$product->set('pricein', $productPrice['in']);
			$product->set('priceex', $productPrice['ex']);
			
			if ($config['asArray'] == true) {
				$products[] = $product->toArray();
			} else {
				$products[] = $product;
			}
		}
		
		return array(
			'data' => $products,
			'total' => (int) $count
		);
    }
    
    /**
     * Get SKU:(Stock Keeping Units) from the parent product
     *
     * @access public
     * @param int $parentId The id of the parent product to be fetched
     * @param int $categoryId The categoryId in which the SKUs reside
     * @param array $config Configuration array, see function head for options
     * @return array SKUs
     */
    public function getSKUs($parentId, $categoryId, $config=array()) {
    	$skus = array();
    	$config['hideSKU'] = false;
		
		$stack = $this->getProducts($categoryId, array(
			'parent' => $parentId,
			'hideSKU' => false
		));
		$stack = $stack['data'];
		
		foreach($stack as $sku) {
			if ($config['asArray'] == true) {
				$skus[] = $sku;
				continue;
			}
			
			$skus[] = $sku;
		}
		
		return $skus;
    }
    
     /**
     * Get SKU:(Stock Keeping Units) from the parent product
     *
     * @access public
     * @param int $parentId The id of the parent product to be fetched
     * @param int $categoryId The categoryId in which the SKUs reside
     * @param array $config Configuration array, see function head for options
     * @return array SKUs
     */
    public function getSKUsOptions($productId, $categoryId, $config=array()) {
    	$options = array();
    	
    	$config['hideSKU'] = false;
    	$config['showUnique'] = $this->modx->getOption('showUnique', $config, true);
    	
    	$product = $this->getProduct($productId);
		$shop = $this->getShop();
		
		$productOptions = $product->getMany('ProductOption');
		foreach($productOptions as $productOption) {
			$option = $productOption->getOne('Option');
			$value = $productOption->getOne('OptionValue');
			
			$options[$option->get('id')] = array(
				'id' => $option->get('id'),
				'name' => $option->get('name'),
				'values' => array(
					($config['showUnique'] == true ? $value->get('id') : 0) => array(
						'id' => $value->get('id'),
						'value' => $value->get('value')
					)
				)
			); 
		}
		
		$skus = $this->getProducts($categoryId, array(
			'parent' => $productId,
			'hideSKU' => false
		));
		$skus = $skus['data'];
		
		foreach($skus as $sku) {
			$skuOptions = $sku->getMany('ProductOption');
			
			foreach($skuOptions as $skuOption) {
				$option = $skuOption->getOne('Option');
				$value = $skuOption->getOne('OptionValue');
				
				if ($config['showUnique'] == true) {
					$options[$option->get('id')]['values'][$value->get('id')] = array(
						'id' => $value->get('id'),
						'value' => $value->get('value')
					);
				} else {
					$options[$option->get('id')]['values'][] = array(
						'id' => $value->get('id'),
						'value' => $value->get('value')
					);
				}
			}
		}
		
		return $options;
    }
    
    /**
     * Get category tree
     *
     * @access public
     * @param string $parent The ID of the parent (defaults to 0)
     * @param string $config Config
     * @return array The categories
     */
    public function getCategoryNodes($parent=0, $config=array()) {
    	if (!isset($config['shopid'])) {
    		$config['shopid'] = $_REQUEST['shopid'];
    	}
    	
    	$shopCurrency = $this->getShopSetting('currency', $config['shopid']);
    	$productView = false;
    	
    	if (!isset($config['hideInactive'])) {
    		$config['hideInactive'] = false;
    	}
    	
    	$dependencies['parent'] = $parent;
    	if ($config['hideInactive']) {
    		$dependencies['active']	= 1;
    	}
    	if (isset($config['shopid'])) {
    		$dependencies['shopid'] = $config['shopid'];	
    	}
    	if (isset($config['productview'])) {
    		$productView = $config['productview'];
    	}    	
    	
    	$sortBy = $this->modx->getOption('sortBy', $config, 'sort');
    	$sort = $this->modx->getOption('sort', $config, 'ASC');
    	
    	$query = $this->modx->newQuery('vcCategory', $dependencies);
    	$query->sortby($sortBy, $sort);
    	
    	$categories = $this->modx->getCollection('vcCategory', $query);
		$taxesCategory = $this->getShopSetting('taxesCategory', $config['shopid']);
		    	
    	$output = array();
    	foreach($categories as $category) {
    		if ($productView) {
    			if ($category->get('id') == $taxesCategory || $category->get('parent') == $taxesCategory) {
    				continue;	
    			}
    		}
    		
			if ($category->get('pricechange') != 0) {
				if ($category->get('pricepercent') == 0) {
					if ($priceChange > 0) {
						$sign = '+';
					} else {
						$sign = '-';
					}
					
					$priceChange = $sign.' '.$shopCurrency.abs($category->get('pricechange'));
				} else {
					if ($priceChange > 0) {
						$sign = '+';
					} else {
						$sign = '-';
					}
					
					$priceChange = $sign.' '.abs($category->get('pricechange')).'%';
				}
			} else {
				$priceChange = 'no change';
			}
			
			$children = $this->getCategoryNodes($category->get('id'), $config);
			$leaf = true;
			if (sizeof($children) > 0) {
				$leaf = false;
			}
			
			if ($productView) {
				$link = $this->modx->getObject('vcProductCategory', array(
					'productid' => $config['productId'],
					'categoryid' => $category->get('id'),
					'shopid' => $config['shopid']
				));
				if ($link != null) {
					$checked = true;	
				} else {
					$checked = false;	
				}
			} else {
				$checked = $category->get('active');
			}
			
    		$output[] = array(
				'text' => $category->get('name').' (ID: '.$category->get('id').', price: '.$priceChange.')',
				'pricechange' => $category->get('pricechange'),
				'pricepercent' => $category->get('pricepercent'),
				'id' => 'category:'.$category->get('id').'|parent:'.$category->get('parent'),
				'cls' => 'folder',
				'qtip' => '',
				'checked' => $checked,
				'sort' => $category->get('sort'),
				'children' => $children,
				'allowChildren' => true
    		);
    	}
    	
    	return $output;
    }
    
    /**
     * Get product tree
     *
     * @access public
     * @param string $parent The ID of the parent (defaults to 0)
     * @param string $config Config
     * @return array The categories
     */
    public function getProductNodes($parent=0, $config=array()) {
    	$dependencies['parent'] = $parent;
    	if (isset($config['shopid'])) {
    		$dependencies['shopid'] = $config['shopid'];	
    	}
    	if (isset($config['parent'])) {
    		$dependencies['parent'] = $config['parent'];	
    	}
    	
    	$taxesCategory = $this->getShopSetting('taxesCategory', $config['shopid']);
    	$hideSkus = $this->modx->getOption('hideskus', $config, 0);

    	$sortBy = $this->modx->getOption('sortBy', $config, 'sort');
    	$sort = $this->modx->getOption('sort', $config, 'ASC');
    	
    	$query = $this->modx->newQuery('vcCategory', $dependencies);
    	$query->sortby('sort', 'ASC');
    	
    	$categories = $this->modx->getCollection('vcCategory', $query);
    	
    	$output = array();
    	foreach($categories as $category) {
    		if ($category->get('id') == $taxesCategory || $category->get('parent') == $taxesCategory) {
    			continue;	
    		}
			//$children = $this->getProductNodes($category->get('id'), $config);
		
    		// Add all the nodes
    		$output[] = array(
				'text' => $category->get('name').' ('.$category->get('id').')',
				'id' => 'category:'.$category->get('id').'|parent:'.$category->get('parent'),
				'cls' => 'folder',
				'qtip' => $category->get('description'),
				'allowChildren' => true,
				'allowDrag' => false,
				'allowDrop' => true,
				'sort' => 0
    		);
    	}
    	
		// Fetch categories products
		if ($dependencies['parent'] != 0) {
			$productQuery = $this->modx->newQuery('vcProductCategory', array(
				'categoryid' => $dependencies['parent']
			));
			$productQuery->sortby($sortBy, $sort);
			
			$productLinks = $this->modx->getCollection('vcProductCategory', $productQuery);
			
			// Fetch each product belonging to this category
			foreach($productLinks as $productLink) {
				$product = $productLink->getOne('Product');
				
				// This shouldn't happen...but in case it does let's log it ;)
				if ($product == null) {
					$this->logError('Could not find product for link, product link removed.', __FILE__, __LINE__, 1);
					$productLink->remove();
					continue;
				}
				
				if ($product->get('active') == 2) {
					continue;
				}
				
				if ($hideSkus == 1 && $product->get('parent') != 0) {
					continue;	
				}
				
				if (!$this->isPublished($product)) {
					$extraClass .= ' unpublished';
				} else {
					if ($product->get('active') == 0) {
						$extraClass = ' vc-inactive';
					}	
				}
				
	    		$output[] = array(
					'text' => $product->get('name').' ('.$product->get('id').')',
					'id' => $product->get('id'),
					'cls' => 'file'.$extraClass,
					'qtip' => '',
					'allowChildren' => false,
					'leaf' => true,
					'sort' => $productLink->get('sort')
	    		);
			}
		}
	
    	return $output;
    }
    
    /**
     * Check if an item is active or not by publish date and set active/inactive 
     * when needed.
     * 
     * @access public
     * @param object $object The xPDO object (Must have "unpublishdate, publishdate, active" columns)
     * @return bool true or false if object is active or not
     */
    public function isPublished($object) {
    	$currentTime = time();
    	$startTime = strtotime($object->get('publishdate'));
    	$endTime = strtotime($object->get('unpublishdate'));
    	
		if ($startTime != 0 && $endTime == 0) {
			if ($currentTime > $startTime) {
				if ($object->get('active') == 0) {
					$object->set('active', 1);
					$object->save();	
				}
				return true;
			}
		} elseif ($startTime == 0 && $endTime != 0) {
			if ($currentTime < $endTime) {
				if ($object->get('active') == 0) {
					$object->set('active', 1);
					$object->save();	
				}
				return true;	
			}
		} elseif ($startTime != 0 && $endTime != 0) {
			if ($currentTime > $startTime && $currentTime < $endTime) {
				if ($object->get('active') == 0) {
					$object->set('active', 1);
					$object->save();	
				}
	    		return true;	
	    	}
		} elseif ($startTime == 0 && $endTime == 0) {
			if ($object->get('active') == 1) {
				return true;
			}
		}
		
		if ($object->get('active') == 1) {
			$object->set('active', 0);
			$object->save();	
		}
		   	
    	return false;
    }
    
    /**
     * Removes a category and ALL it's children by ID
     *
     * @access public
     * @param int $id The id of the category
     * @return bool true
     */
    public function removeCategory($id) {
    	$targetDir = $this->config['assetsBasePath'].'web/images/products/';
    	
    	$categories = $this->modx->getCollection('vcCategory', array(
    		'parent' => $id
    	));
    	
    	// Get all products and remove thumbnails
    	$productLinks = $this->modx->getCollection('vcProductCategory', array(
    		'categoryid' => $id
    	));
    	
    	foreach($productLinks as $productLink) {
    		// Check if product is linked to only one category, if so delete it and it's thumbs
    		$otherLink = $this->modx->getObject('vcProductCategory', array(
    			'productid' => $productLink->get('productid')
    		));
    		
    		if ($otherLink != null) {
    			continue;	
    		}
    		
    		$product = $productLink->getOne('Product');
    		
    		if ($product == null) {
    			continue;	
    		}
    		
    		$productImages = $product->get('pictures');
			$thumbnailArray = $this->getThumbnailSettings($product->get('shopid'));
						
			$product->remove();	
			
			foreach($productImages as $image) {
				foreach($thumbnailArray as $thumb) {
					$path = $targetDir.$thumb['prefix'].$image;
					if (is_file($path)) {
						@unlink($path);
					}
				}	
			}
			
    	}
    	
    	foreach($categories as $category) {
    		$this->removeCategory($category->get('id'));
    		$category->remove();
    	}
    	
		$cat = $this->modx->getObject('vcCategory', $id);
		if ($cat != null) {
			$cat->remove();
		}
		
		return true;
    }
    
    /**
     * Returns a full module config array
     *
     * @access public
     * @param int $id 
     * @param array $config The module config
     */
    public function getModuleConfig($id) {
    	static $moduleConfigs;
    	
    	if (!isset($moduleConfigs)) {
    		$moduleConfigs = array();
    	}
    	
    	if (isset($moduleConfigs[$id])) {
    		return $moduleConfigs[$id];	
    	}
    	
    	$module = $this->modx->getObject('vcModule', $id);
    	
    	if ($module == null) {
    		return false;	
    	}
    	
    	$controller = $module->get('controller');
    	$type = $module->get('type');
    	
    	// b03tz modified $modulePath instead of dirname to corePath
    	$modulePath = $this->config['corePath'].'modules/'.dirname($type.'/'.$controller);
    	$configPath = $modulePath.'/config.php';
    	$currentConfig = $module->get('config');
		
    	// Check for default config
    	if ($module->get('type') == 'shipping' || $module->get('type') == 'payment') {
    		$defaultConfig = $this->config['basePath'].'modules/'.$module->get('type').'/config.php';
    	}
    	
    	if (isset($defaultConfig) && is_file($defaultConfig)) {
    		include $defaultConfig;
    	}
    	
    	$config = array();
    	if (is_file($configPath)) {
    		include $configPath;
    		if (isset($moduleProperties) && is_array($moduleProperties)) {
    			$moduleProperties = array_merge($defaultProperties, $moduleProperties);
    		} else {
    			$moduleProperties = $defaultProperties;	
    		}
    	} else {
    		$moduleProperties = $defaultProperties;	
    	}
    		
		foreach($moduleProperties as $property) {
			$currentConfig[$property['key']] = $currentConfig[$property['key']];
			if (isset($currentConfig[$property['key']])) {
				$value = $currentConfig[$property['key']];
			} else {
				$value = $property['defaultValue'];	
			}
			
			$property['value'] = $value;
			$config[] = $property;
		}
    	
    	$moduleConfigs[$id] = $config;
    	
    	return $config;
    }
    
    /**
     * Returns a single module setting (either defaultValue or when filled in, the value)
     *
     * @access public
     * @param int $id 
     * @param string $settingName
     * @param mixed 
     */
    public function getModuleSetting($id, $settingName) {
    	$config = $this->getModuleConfig($id);
    	
    	foreach($config as $key) {
    		if ($key['key'] == $settingName) {
    			return $key['value'];	
    		}	
    	}
    	
    	return false;
    }
    
    public function getModule($id, $config=array()) {
    	$query = $this->modx->newQuery('vcModule');
		$query->where(array(
			'id' => (int) $id
		));
		
		$module = $this->modx->getObject('vcModule', $query);
		
		return $module;
    }
    
    /**
     * Return the thumbnail settings for a single shop
     *
     * @access public
     * @param int $shopId 
     * @param bool $category False for product thumbnail settings, true for category thumbnail settings
     */
    public function getThumbnailSettings($shopId, $category=false) {
    	if ($category) {
    		$thumbnailArray = $this->getShopSetting('categoryThumbnails', $shopId);
    	} else {
			$thumbnailArray = $this->getShopSetting('thumbnails', $shopId);
    	}
		$thumbnailArray = explode("\n", $thumbnailArray);
		foreach($thumbnailArray as $key => $value) {
			$outputArray = array();
			$value = explode(',', $value);
			foreach($value as $subKey => $subValue) {
				$subValue = explode('=', $subValue);
				$outputArray[$subValue[0]] = $subValue[1];
			}
			$thumbnailArray[$key] = $outputArray;
		}
		
		return $thumbnailArray;
    }
    
     /**
     * Get a custom field from a product
     *
     * @access public
     * @param int $id Product ID
     * @param string $key Keyname of the custom field
     * @return string The custom field's value
     */
    public function getProductCustomField($id, $key) {
    	$product = $this->modx->getObject('vcProduct', $id);
    	$productArray = $product->toArray();
        
    	if (isset($productArray['customfields'][$key])) {
    		return $productArray['customfields'][$key];
    	}
    	
    	return false;
    }
    
     /**
     * Set a custom field from a product
     *
     * @access public
     * @param int $id Product ID
     * @param string $key Keyname of the custom field
     * @param string $value Value of the custom field
     * @return bool xPDO object saved or not
     */
    public function setProductCustomField($id, $key, $value) {
    	$product = $this->modx->getObject('vcProduct', $id);
    	$productArray = $product->toArray();
    	$productArray['customfields'][$key] = $value;
	   	$product->set('customfields', $productArray['customfields']);
    	
    	return $product->save();
    }
    
    /**
     * Fire a visioncart specific plugin
     *
     * @access public
     * @param string $snippetPrefix Prefix of the snippet
     * @param string $event The event that should match the parameter
     * @param array $snippetParameters Parameters that should be passed to the snippet
     * @return bool Success
     */
    public function fireEvent($snippetPrefix, $event, $snippetParameters=array()) {
		$snippets = $this->modx->getCollection('modSnippet', array('name:LIKE' => $snippetPrefix.'%'));
		
		foreach ($snippets as $snippet) {
			$returnValue = json_decode($this->modx->runSnippet($snippet->get('name'), array(
				'vcAction' => 'getParams'
			)));
			
			if (isset($returnValue->active)) {
				if ($returnValue->active == false) {
					continue;	
				}	
			}
			
			if (!isset($snippetParameters['vcAction'])) {
				$snippetParameters['vcAction'] = 'process';	
			}
			
			if ($returnValue->event == $event || $event == '') {
				$returnValue = $this->modx->runSnippet($snippet->get('name'), $snippetParameters);
			}
	    }
    }
    
    /**
     * Send (bulk) email through modMail
     *
     * @access public
     * @param array $config Input config array
     * @return bool Success
     */
    public function sendMail($config=array()) {
    	$config['attachments'] = $this->modx->getOption('attachments', $config, array());

    	if (!isset($config['placeholders'])) {
    		$config['placeholders'] = array();	
    	}
    	
    	if (!isset($config['shopid'])) {
    		return false;	
    	}

    	if (isset($config['message'])) {
    		$message = $config['message'];
    		
    		// Prepare placeholders
    		foreach($config['placeholders'] as $key => $value) {
    			$message = str_replace('[[+'.$key.']]', $value, $message);
    		}
    	} else {
			$message = $this->modx->getChunk($config['chunk'], $config['placeholders']);
    	}
    	
    	$subject = $config['subject'];
    		
		// Prepare placeholders
		foreach($config['placeholders'] as $key => $value) {
			$subject = str_replace('[[+'.$key.']]', $value, $subject);
		}
    	
		$this->modx->getService('mail', 'mail.modPHPMailer');
		$this->modx->mail->set(modMail::MAIL_BODY, $message);
		$this->modx->mail->set(modMail::MAIL_FROM, $this->getShopSetting('emailFromAddress', $config['shopid']));
		$this->modx->mail->set(modMail::MAIL_FROM_NAME, $this->getShopSetting('emailFromName', $config['shopid']));
		$this->modx->mail->set(modMail::MAIL_SENDER, $this->getShopSetting('emailFromAddress', $config['shopid']));
		$this->modx->mail->set(modMail::MAIL_SUBJECT, $subject);
        
		if (is_array($config['to'])) {
			foreach($config['to'] as $email) {
				$this->modx->mail->address('to', $email);
			}
		} else {
			$this->modx->mail->address('to', $config['to']);
		}
		
		$this->modx->mail->address('reply-to', $this->getShopSetting('emailFromAddress', $config['shopid']));
		$this->modx->mail->setHTML(true);
		
		if (sizeof($config['attachments']) > 0) {
			foreach($config['attachments'] as $attachment) {
				if (!isset($attachment['type'])) {
					$attachment['type'] = 'application/octet-stream';	
				}
				if (!isset($attachment['name'])) {
					$attachment['name'] = basename($attachment['path']);
				}
				$this->modx->mail->attach($attachment['path'], $attachment['name'], 'base64', $attachment['type']);
			}
		}
		
		$config['mailer'] = $this->modx->mail;
		$this->fireEvent('vcEventMail', 'before', $config);
		
		$success = $this->modx->mail->send();
		
		$config['success'] = $success;
		$this->fireEvent('vcEventMail', 'after', $config);
		
		if (!$success) {
		    $this->modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: '.$err);
		}
		
		$this->modx->mail->reset();
		
		return $success;
    }
	
	/**
     * A makeUrl function adopted from MODx itself, with minor ajustments
     * For more information, please refer to the specified access and param parameters
     *
     * @access public
     * @param int $id Product/Category link
     * @return string The processed content
     */
    public function buildQuerystring($array, $config=array()) {
    	$config['encoded'] = $this->modx->getOption('encoded', $config, false);
    	$config['strip'] = $this->modx->getOption('strip', $config, array());
    	$config['overwrite'] = $this->modx->getOption('overwrite', $config, array());
    	$config['prefix'] = $this->modx->getOption('prefix', $config, '?');
    	$config['suffix'] = $this->modx->getOption('suffix', $config, false);
    	
    	if (!empty($config['strip'])) {
    		foreach($config['strip'] as $value) {
    			unset($array[$value]);
    		}
    	}
    	
    	if (!empty($config['overwrite'])) {
    		foreach($config['overwrite'] as $key => $value) {
    			$array[$key] = $value;
    		}
    	}
    	
    	$string = $config['prefix'].http_build_query($array);
    	
    	if ($config['suffix'] != false) {
    		$string .= (string) $config['suffix'];
    	}
    	
    	if ($config['encoded'] == true) {
    		return $string;
    	}
    	
    	return rawurldecode($string);
   	}
    
    /**
     * A makeUrl function adopted from MODx itself, with minor ajustments
     * For more information, please refer to the specified access and param parameters
     *
     * @access public
     * @param int $id Product/Category link
     * @return string The processed content
     */
    public function makeUrl($config=array()) {
        if (empty($config)) {
            return false;
        }
        
        $config['suffix'] = $this->modx->getOption('suffix', $config, '.html');
        $config['shopId'] = $this->modx->getOption('shopId', $config, 0);
        
        if ($config['shopId'] == 0) {
	    	$shop = $this->getShop();
	    	
	    	if ($shop == null) {
    			return false;
    		}
    		
	    	$config['shopId'] = $shop->get('id');
	    	unset($shop);
		}
        
        // If the config shopId has been set and is numeric, (re)fetch the current shop by Id
        if (!isset($shop) && isset($config['shopId']) && is_numeric($config['shopId'])) {
        	$shop = $this->getShop(array(
	 			'type' => 'id',
	 			'value' => $config['shopId']
			));
        }
        
        if (!isset($config['scheme']) || !in_array($config['scheme'], array(-1, 'full', 'http', 'https'))) {
            $config['scheme'] = -1;
        }
    
        switch($config['scheme']) {
            case -1:
                $url = '/';
                break;
            case 'full':
                $url = $this->modx->getOption('site_url');
                break;
            case 'http':
            case 'https':
                $url = $config['scheme'].'://'.$_SERVER['HTTP_HOST'].'/';
                break;
        }
        
        $url .= $shop->get('alias').'/';
        
        // Check wether the type points towards a product or a category link
        if (isset($config['productCategory'])) {
            $link = $this->modx->getObject('vcProductCategory', $config['productCategory']);
            
            if ($link == null) {
                return '';
            }
            
            $product = $link->getOne('Product');
            $categories = array_reverse($this->_categoryUltimateParent(array(
                'parent' => $link->getOne('Category')->get('id'),
                'shopId' => $config['shopId']
            )));
            
            foreach($categories as $category) {
                $url .= $category->get('alias').'/';
            }
            
            $url .= $product->get('alias').'-'.$product->get('id').$config['suffix'];
        } else {
            $categories = array_reverse($this->_categoryUltimateParent(array(
                'parent' => $config['categoryId'],
                'shopId' => $config['shopId']
            )));
            
            foreach($categories as $category) {
                $url .= $category->get('alias').'/';
            }
        }
        
        return $url;
    }
   
    /**
     * wayFinder
     * VisionCart's very own wayFinder. See documentation for features.
     * No relation to Splittingred's WayFinder
     *
     * @access public
     * @param array $scriptProperties Properties to take into account
	 * @param array $config Configuration (overwrites $scriptProperties) 
     * @return string Processed wayFinder list
     */
    public function wayFinder($scriptProperties=array(), $config=array()) {
    	$output = array(
    		'master' => '', 
    		'return' => '',
    		'innerChunk' => ''
    	);
    	
    	// Settings
    	if (isset($config['shopId'])) {
    		$config['shopId'] = $this->modx->getOption('shopId', $config, 0);
    	} else {
    		$config['shopId'] = $this->modx->getOption('shopId', $scriptProperties, 0);
    	}
    	$config['config'] = $this->modx->getOption('config', $config, 'default');
    	$config['hideInactive'] = $this->modx->getOption('hideInactive', $config, true);
    	$config['hideSKU'] = $this->modx->getOption('hideSKU', $config, true);
 		$config['parent'] = $this->modx->getOption('parent', $config, 0);
 		$config['shopId'] = $this->modx->getOption('shopId', $config, 0);
 		$config['children'] = $this->modx->getOption('children', $config, array());
 		$config['depth'] = $this->modx->getOption('depth', $config, 0);
 		$config['limit'] = $this->modx->getOption('limit', $config, 0);
 		$config['offset'] = $this->modx->getOption('offset', $config, 0);
 		$config['productSort'] = $this->modx->getOption('productSort', $config, 'ASC');
 		$config['productSortBy'] = $this->modx->getOption('productSortBy', $config, 'sort');
 		$config['categorySort'] = $this->modx->getOption('categorySort', $config, 'ASC');
 		$config['categorySortBy'] = $this->modx->getOption('categorySortBy', $config, 'sort');
 		$config['internalDepth'] = $this->modx->getOption('internalDepth', $config, 0);
 		$config['internalLimit'] = $this->modx->getOption('internalLimit', $config, 0);
 		$config['showProducts'] = $this->modx->getOption('showProducts', $config, 1);
 		$config['excludeCategories'] = $this->modx->getOption('excludeCategories', $config, '');

 		// Template
		$params['wrapperTpl'] = $this->modx->getOption('wrapperTpl', $config, '');
		$params['categoryTpl'] = $this->modx->getOption('categoryTpl', $config, '');
		$params['firstCategoryTpl'] = $this->modx->getOption('firstCategoryTpl', $config, '');
		$params['lastCategoryTpl'] = $this->modx->getOption('lastCategoryTpl', $config, '');
		$params['activeCategoryTpl'] = $this->modx->getOption('activeCategoryTpl', $config, '');
		$params['activeParentTpl'] = $this->modx->getOption('activeParentTpl', $config, '');
		$params['categoryClass'] = $this->modx->getOption('categoryClass', $config, '');
		$params['activeCategoryClass'] = $this->modx->getOption('activeCategoryClass', $config, '');
		$params['activeParentClass'] = $this->modx->getOption('activeParentClass', $config, '');
		$params['firstCategoryClass'] = $this->modx->getOption('firstCategoryClass', $config, '');
		$params['lastCategoryClass'] = $this->modx->getOption('lastCategoryClass', $config, '');
		$params['productTpl'] = $this->modx->getOption('productTpl', $config, '');
		$params['activeProductTpl'] = $this->modx->getOption('activeProductTpl', $config, '');
		$params['firstProductTpl'] = $this->modx->getOption('firstProductTpl', $config, '');
		$params['lastProductTpl'] = $this->modx->getOption('lastProductTpl', $config, '');
		$params['productClass'] = $this->modx->getOption('productClass', $config, '');
		$params['activeProductClass'] = $this->modx->getOption('activeProductClass', $config, '');
		$params['firstProductClass'] = $this->modx->getOption('firstProductClass', $config, '');
		$params['lastProductClass'] = $this->modx->getOption('lastProductClass', $config, '');
		$params['levelClass'] = $this->modx->getOption('levelClass', $config, '');
		$params['levelOffset'] = $this->modx->getOption('levelOffset', $config, 0);
		
 		$config['firstClassFlag'] = false;
 		$config['internalItemCount'] = 1;

	 	if ($config['shopId'] == 0) {
 			$shop = $this->getShop();
 			
 			if ($shop == null) {
    			return false;
    		}
    		    		
			$config['shopId'] = $shop->get('id');
			unset($shop);
 		}

 		// Set tax category	
 		$config['taxCategory'] = $this->modx->getOption('taxCategory', $config, $this->getShopSetting('taxesCategory', $config['shopId']));
 		
 		$config = array_merge($config, $this->getConfigFile($config['shopId'], 'wayFinder', null, array(
			'config' => $config['config']
	 	)), $scriptProperties);
 		
 		$query = $this->modx->newQuery('vcCategory');
 		$query->where(array(
		 	'parent' => $config['parent'],
		 	'shopid' => $config['shopId']
		 ));
		 
		if ($config['hideInactive'] == true) {
			$query->andCondition(array(
				'active' => 1
			));
		}
		
 		$query->sortby($config['categorySortBy'], $config['categorySort']);
 		
 		if ($config['limit'] != 0 && $config['internalDepth'] == 0) {
 			$query->limit($config['limit'], $config['offset']);	
 		}
 		
 		$categories = $this->modx->getCollection('vcCategory', $query);
 		
 		$config['excludeCategories'] = explode(',', (string) $config['excludeCategories']);
 		$config['excludeCategories'][] = $config['taxCategory'];
 		
 		$config['lastItem'] = $this->modx->getOption('lastItem', $config, end($categories));
 		
 		foreach($categories as $category) {
 			if (!empty($config['excludeCategories']) && in_array($category->get('id'), $config['excludeCategories'])) {
 				continue;
 			}
 			
 			if ($config['depth'] != 0 && $config['depth'] <= $config['internalDepth']) {
 				continue;	
 			}
 			
 			$tpl = $config['categoryTpl'];
 			$classes = $config['categoryClass'].' ';

 			if (!$config['firstClassFlag']) {
 				$classes .= $config['firstCategoryClass'].' ';
				if ($config['firstCategoryTpl'] != '') {
 					$tpl = $config['firstCategoryTpl'];
 				} else {
 					$tpl = $config['categoryTpl'];	
 				}
 				$config['firstClassFlag'] = true;
 			}
 			
 			//if (isset($config['lastItem']) && $config['lastItem'] == $category) {
			if (count($categories) == $config['internalItemCount']) {
 				$classes .= $config['lastCategoryClass'].' ';
 				if ($config['lastCategoryTpl'] != '') {
 					$tpl = $config['lastCategoryTpl'];
 				} else {
 					$tpl = $config['categoryTpl'];	
 				}
 			}
 			
 			if (isset($this->router['categories'])) {
	 			if (in_array($category->get('alias'), $this->router['categories']) && ($category->get('alias') != end($this->router['categories']) || $this->product != null)) {
	 				$classes .= $config['activeParentClass'].' ';
	 				if ($config['activeParentTpl'] != '') {
 						$tpl = $config['activeParentTpl'];
	 				} else {
	 					$tpl = $config['categoryTpl'];	
	 				}
	 			}
 			}
 			
 			if (isset($this->router['categories'])) {
	 			if ($this->product == null && $category->get('alias') == end($this->router['categories'])) {
	 				$classes .= $config['activeCategoryClass'].' ';
	 				if ($config['activeCategoryTpl'] != '') {
 						$tpl = $config['activeCategoryTpl'];
	 				} else {
	 					$tpl = $config['categoryTpl'];	
	 				}
	 			}
 			}
 			
 			if ($config['levelClass'] != '') {
 				if ($config['internalDepth'] == 0 && $config['levelOffset'] > 0) {
 					$classes .= $config['levelClass'].($config['internalDepth']+2+$params['levelOffset']).' ';
 				} else {
 					$classes .= $config['levelClass'].($config['internalDepth']+1+$params['levelOffset']).' ';
 				}
 			}
 			
 			// Set classes
 			$category->set('classes', substr($classes, 0, -1));
 			 			
 			if ($config['showProducts'] == 1) {
	 			// Fetch child products
	 			$products = $this->getProducts($category->get('id'), array(
	 				'sort' => $config['productSort'],
	 				'sortBy' => $config['productSortBy'],
	 				'shopId' => $config['shopId'],
	 				'hideInactive' => $config['hideInactive']
	 			));
	 			//$product['data'] = $products;
				 $products = $products['data']; 			
 			}

 			// Overwrite config
 			$newConfig = $config;
 			$newConfig['children'] = $products;
 			$newConfig['parent'] = $category->get('id');
 			$scriptProperties['parent'] = $newConfig['parent'];
 			
 			$newConfig['internalDepth'] += 1;
 			if ($config['depth'] != 0 && $newConfig['internalDepth'] <= $config['depth']) {
 				$output['innerChunk'] = $this->wayFinder($scriptProperties, $newConfig);
			} else {
				$output['innerChunk'] = $this->wayFinder($scriptProperties, $newConfig);
			}
 			$newConfig['internalDepth'] -= 1;
 			
 			$config['internalItemCount']++;
 			
 			$category->set('url', $this->makeUrl(array(
 				'categoryId' => $category->get('id'),
 				'shopId' => $config['shopId']
 			)));
 			
 			$output['master'] .= $this->parseChunk($tpl, array(
 				'item' => $category->toArray(),
 				'children' => $output['innerChunk']
 			));
			
			unset($newConfig);
 		}
 		
 		if ($config['showProducts'] == 1) {
 			$config['internalItemCount'] = 1;
 			
	 		foreach($config['children'] as $product) {
	 			$link = $this->modx->getObject('vcProductCategory', array(
	 				'categoryid' => $config['parent'],
	 				'productid' => $product->get('id'),
	 				'shopid' => $config['shopId']
	 			));
	 			
	 			$product->set('url', $this->makeUrl(array(
	 				'productCategory' => $link->get('id'),
	 				'shopId' => $config['shopId']
	 			)));
	 			
	 			// Set classes
	 			$classes = $config['productClass'].' ';
	 			$tpl = $config['productTpl'];
	 			
	 			if ($config['internalItemCount'] == 1) {
	 				$classes .= $config['firstProductClass'].' ';
	 				if ($config['firstProductTpl'] != '') {
	 					$tpl = $config['firstProductTpl'];
	 				} else {
	 					$tpl = $config['productTpl'];	
	 				}
	 			}
	 			
	 			if (count($config['children']) != 1 && $config['internalItemCount'] == count($config['children'])) {
	 				$classes .= $config['lastProductClass'].' ';
	 				if ($config['lastProductTpl'] != '') {
	 					$tpl = $config['lastProductTpl'];
	 				} else {
	 					$tpl = $config['productTpl'];	
	 				}
	 			}
	 			
 				if ($this->product != null && $product->get('id') == $this->router['product']['id'] && end($this->router['categories']) == $link->getOne('Category')->get('alias')) {
 					$classes .= $config['activeProductClass'].' ';
 					if ($config['activeProductTpl'] != '') {
	 					$tpl = $config['activeProductTpl'];
	 				} else {
	 					$tpl = $config['productTpl'];	
	 				}
 				}
 				
 				$product->set('classes', substr($classes, 0, -1));
 				
 				$output['master'] .= $this->parseChunk($tpl, array(
	 				'item' => $product->toArray()
	 			));
	 			
	 			$config['internalItemCount']++;
	 		}
 		}
    	
 		if (count($config['children']) != 0 || count($categories) != 0) {
 			$output['return'] = $this->parseChunk($config['wrapperTpl'], array(
 				'innerChunk' => $output['master']
 			));
 		}
 		
 		return $output['return'];
    }
    
    /**
     * multiNaturalSort
     * Sorts a multi dimensional array
     *
     * @access public
     * @param array $array The array to sort
	 * @param string $column The column in the array
	 * @param string $order ASC or DESC sort 
     * @return string The processed content of the Chunk
     */
    function multiNaturalSort($array, $column, $order='asc') {
		if (!is_array($array)) {
			return $array;	
		}
	
		$this->column = $column;
		$this->order = $order;
		
		uasort($array, array($this, 'compareNat'));
		
		if (strtolower($order) == 'desc') {
			$array = array_reverse($array);
		}
		
		return $array;
	}
	
	/**
     * compareNat
     * 
     *
     * @access private
     * @param $x First matcher
     * @param $y Second matcher
     * @return int Compared weight
     */
	private function compareNat($x, $y) {
		$col = $this->column;
		
		if (strpos($col, '.')) {
			$col = explode('.', $col);
			$output = '';
			foreach($col as $column) {
				$output .= '[\''.$column.'\']';	
			}
			$col = $output;
		}
		
		if (is_object($x)) {
			$xValue = $x->get($col);
		} else {
			if (substr($col, 0, 1) == '[') {
				eval('$xValue = $x'.$col.';');
			} else {
				$xValue = $x[$col];
			}
		}
		
		if (is_object($y)) {
			$yValue = $y->get($col);
		} else {
			if (substr($col, 0, 1) == '[') {
				eval('$yValue = $y'.$col.';');
			} else {
				$yValue = $y[$col];
			}
		}
		
		if ($col != '') {
			return strnatcasecmp($xValue, $yValue);
		} else {
			return 0;	
		}
	}
	
	/**
     * export
     * Get all categories that belong to the provided shopId
     *
     * @access public
     * @param int $shopId The ID of the shop
     * @param array $config Configuration 
     * @return arr/obj The object or array if provided in the config
     */
	public function getCategories($shopId, $config=array()) {
		$categories = array();
		
		$config['asArray'] = $this->modx->getOption('asArray', $config, false);
		$query = $this->modx->newQuery('vcCategory');
		$query->where(array(
			'shopid' => $shopId,
			'parent' => $config['parent'],
			'active:!=' => 2
		));
		
		$query->andCondition(array(
			'active' => 1
		));
		
		$stack = $this->modx->getCollection('vcCategory', $query);
		
		foreach($stack as $category) {
			// Skip the tax category
			if ($category->get('id') == $this->getShopSetting('taxesCategory', $shopId)) {
				continue;
			}
			
			if ($config['asArray'] == true) {
				$categories[] = $category->toArray();
				
				continue;
			}
			
			$categories[] = $category;
		}
		
		return $categories;
	}
	
	/**
     * export
     * Export all producta from chosen categories in a RSS format
     *
     * @access public
     * @param array $config Configuration 
     * @return string The processed content of the Chunk
     */
	public function export($config=array()) {
		$config['shopId'] = $this->modx->getOption('shopId', $config, 0);
		$config['categories'] = $this->modx->getOption('categories', $config, 0);
		$config['format'] = $this->modx->getOption('format', $config, 'rss');
		$config['encoding'] = $this->modx->getOption('encoding', $config, 'text/xml');
		$config['hideSKU'] = (bool) $this->modx->getOption('hideSKU', $config, false);
		
		if ($config['shopId'] == 0 && isset($this->shop) && is_object($this->shop)) {
    		$config['shopId'] = $this->shop->get('id');
    	}
    	
    	$shop = $this->getShop(array(
			'type' => 'id',
			'value' => $config['shopId'] 
		));
    	
		$configFile = $modx->getOption('config', $config, 'default');
    	$config = array_merge($this->getConfigFile($config['shopId'], 'export', null, array('config' => $configFile)), $config);
		
		$output = '';
		if ($config['categories'] == 0) {
			$query = $this->modx->newQuery('vcProduct');
			$query->where(array(
				'shopid' => $config['shopId'],
				'active' => 1
			));
			
			$stack = $this->modx->getCollection('vcProduct', $query);
		} else {
			$stack = array();
			$config['categories'] = explode(',', $config['categories']);
			
			foreach($config['categories'] as $categoryId) {
				$products = $this->getProducts($categoryId, $config);
				
				foreach($products['data'] as $product) {
					$stack[] = $product;
				}
			}
		}
		
		foreach($stack as $product) {
			$product->set('link', $this->makeUrl(array(
				'productCategory' => $product->getOne('ProductCategory')->get('id'),
				'shopId' => $config['shopId'],
				'scheme' => 'full'
			)));
			
			$product->set('date', date('c'));
			
			// Calculate discounts
			$productPrice = $vc->calculateProductPrice($product, true);
			$product->set('pricein', $productPrice['in']);
			$product->set('priceex', $productPrice['ex']);
			
			$product = $product->toArray();
			$output .= $this->parseChunk($config['itemTpl'], $product);
		}
    	
    	$output = $this->parseChunk($config['wrapperTpl'], array(
			'content' => $output,
			'title' => $shop->get('name'),
			'description' => $shop->get('description'),
			'link' => $this->modx->makeUrl($this->modx->resource->get('id'), null, null, 'full')
		));
		
		$file = fopen(MODX_BASE_PATH.'xml.txt', 'w+');
		fwrite($file, $output);
		fopen($file);
		
		return $output;
	}
	
	/**
     * exportProducts
     * Export products in a nested array from shop to product level
     *
     * @access public
     * @param array $config Configuration 
     * @return string The processed content of the Chunk
     */
    public function exportProducts($config=array()) {
    	$output = '';
    	$cache = '';
    	
    	$config['shopId'] = $this->modx->getOption('shopId', $config, 0);
		$config['categories'] = $this->modx->getOption('categories', $config, false);
		$config['format'] = $this->modx->getOption('format', $config, 'xml');
		$config['encoding'] = $this->modx->getOption('encoding', $config, 'text/xml');
		$config['hideSKU'] = (bool) $this->modx->getOption('hideSKU', $config, false);
		$config['productKey'] = $this->modx->getOption('productKey', $config, 'product');
		$config['categoryKey'] = $this->modx->getOption('categoryKey', $config, 'category');
		$config['shopColumns'] = $this->modx->getOption('shopColumns', $config, 'name,alias,description,url,categories');
		$config['categoryColumns'] = $this->modx->getOption('categoryColumns', $config, 'name,alias,description,url,products');
		$config['productColumns'] = $this->modx->getOption('productColumns', $config, 'name,alias,description,articlenumber,price,url');

		$config['wrapperTpl'] = $this->getConfigFile($config['shopId'], 'exportProducts', 'wrapperTpl', array('config' => $config['format']));
		$config['detailsTpl'] = $this->getConfigFile($config['shopId'], 'exportProducts', 'detailsTpl', array('config' => $config['format']));
		$config['itemTpl'] = $this->getConfigFile($config['shopId'], 'exportProducts', 'itemTpl', array('config' => $config['format']));
		$config['dataTpl'] = $this->getConfigFile($config['shopId'], 'exportProducts', 'dataTpl', array('config' => $config['format']));
		
		$shop = $this->getShop(array(
			'value' => $config['shopId'],
			'asArray' => true
		));
		
		$shop['url'] = $this->makeUrl(array(
			'shopId' => $config['shopId'],
			'scheme' => 'full'
		));
		
		$categories = array();
		$products = array();
		
		if (!$config['categories'] || $config['categories'] == 0) {
			$query = $this->modx->newQuery('vcCategory');
			$query->where(array(
				'shopid' => $shop['id'],
				'active' => 1
			));
			
			$stack = $this->modx->getCollection('vcCategory', $query);
			foreach($stack as $category) {
				$config['categories'][$category->get('id')] = $category;
			}
		} else {
			$config['categories'] = array_flip(explode(',', $config['categories']));
		}
		
		$config['shopColumns'] = explode(',', $config['shopColumns']);
		$config['categoryColumns'] = explode(',', $config['categoryColumns']);
		$config['productColumns'] = explode(',', $config['productColumns']);
		
		foreach($config['categories'] as $id => $category) {
			if (!is_numeric($id)) {
				continue;
			}
			
			if (!is_object($category)) {
				$category = $this->getCategory($id, array(
					'type' => 'id'
				));
			}
			
			$stack = $this->getProducts($category->get('id'), array(
				'shopId' => $category->get('shopid'),
				'hideSKU' => $config['hideSKU']
			));
			
			foreach($stack['data'] as $key => $product) {
				$product->set('url', $this->makeUrl(array(
					'productCategory' => $product->getOne('ProductCategory')->get('id'),
					'shopId' => $config['shopId'],
					'scheme' => 'full'
				)));
				
				$stack['data'][$key] = $product->toArray();
			}
			
			$category->set('products', $stack['data']);
			$category->set('url', $this->makeUrl(array(
				'categoryId' => $category->get('id'),
				'shopId' => $config['shopId'],
				'scheme' => 'full'
			)));
			
			$categories[] = $category->toArray();
		}
			
		foreach($categories as $category) {
			$cache = '';
			
			foreach($config['categoryColumns'] as $column) {
				if (isset($category[$column]) && $column != 'products') {
					$cache .= $this->parseChunk($config['dataTpl'], array(
						'key' => $column,
						'value' => htmlentities($category[$column])
					));
				} else {
					$productsOutput = '';
					
					foreach($category[$column] as $product) {
						$productCache = '';
						
						foreach($config['productColumns'] as $productColumn) {
							if (isset($product[$productColumn])) {
								$productCache .= $this->parseChunk($config['dataTpl'], array(
									'key' => $productColumn,
									'value' => $product[$productColumn]
								));
							}
						}
						
						$productsOutput .= $this->parseChunk($config['itemTpl'], array(
							'key' => $config['productKey'],
							'content' =>  $productCache
						));
					}
					
					$cache .= $this->parseChunk($config['dataTpl'], array(
						'key' => $column,
						'value' => $productsOutput
					));
				}
			}
			
			$outputCategories .= $this->parseChunk($config['itemTpl'], array(
				'key' => $config['categoryKey'],
				'content' =>  $cache
			));
		}
		
		$cache = '';
		foreach($config['shopColumns'] as $column) {
			if (isset($shop[$column]) && $column != 'categories') {
				$cache .= $this->parseChunk($config['dataTpl'], array(
					'key' => $column,
					'value' => $shop[$column]
				));
			} else {
				$cache .= $this->parseChunk($config['dataTpl'], array(
					'key' => $column,
					'value' => $outputCategories
				));
			}
		}
		
		$output = $this->parseChunk($config['itemTpl'], array(
			'key' => 'shop',
			'content' =>  $cache
		));
		
		
		$output = $this->parseChunk($config['wrapperTpl'], array(
			'content' => $output,
			'timestamp' => time(),
			'base' => $this->makeUrl(array(
				'scheme' => 'full',
				'shopId' => $config['shopId']
			))
		));
		
		switch($config['format']) {
			case 'xml':
			case 'rss':
			case 'atom':
				header('Content-type: text/xml');
				break;
			case 'csv':
				header('Content-type: text/plain');
				break;
			case 'custom':
				header('Content-type: '.$config['encoding']);
				break;
		}
		
		$file = fopen(MODX_BASE_PATH.'xml.txt', 'w+');
		fwrite($file, $output);
		fopen($file);
		
		return $output;
    }
    
    /**
     * parseChunk
     * Parse a wide range of chunks by their identifier
     *
     * @access public
     * @param string $tpl The portion to parse (@CODE: or a chunk name)
     * @param array $params Placeholders to parse in the chunk
     * @return string Processed output
     */
    public function parseChunk($tpl, $params=array(), $config=array()) {
    	$config['isChunk'] = $this->modx->getOption('isChunk', $config, false);
    	
    	if (substr($tpl, 0, 6) != '@CODE:') {
    		$output = $this->modx->getChunk($tpl, $params);
    	} else {
   			$tpl = substr($tpl, 6);
    		
	    	$chunk = $this->modx->newObject('modChunk');
			$chunk->setCacheable(false);
				
			$output = $chunk->process($params, $tpl);
			unset($chunk);
		}
		
		unset($tpl, $params, $config);
	
		if ($output != '') {
			return $output;
		}
		
		return '';
    }
    
    /**
     * order
     * Return a specific order step (get's called through vcGetOrder)
     *
     * @access public
     * @param array $config
     * @return string The stripped down version of the string 
     */
    public function order($config=array()) {
    	// Update basket if needed
		if (!empty($_REQUEST) && isset($_REQUEST['products']) && !empty($_REQUEST['products'])) {
			$this->modx->runProcessor('basket', array(), array_merge($_REQUEST, array(
				'location' => 'web',
				'processors_path' => $this->config['processorsPath'],
				'return' => 0
			)));
		}

		$step = (int) $this->router['query']['step'];

		if ($step == 0 || $step == '') {
			$step = 1;
		}
    	
    	$processor = $this->modx->runProcessor('order/step'.$step, $config, array(
    		'location' => 'web',
    		'processors_path' => $this->config['processorsPath']
    	));
    	
    	return $processor->getResponse();
    }
    
    /**
     * calculateOrderPrice
     * Calculate order price
     *
     * @access public
     * @param xPDO $order The xPDO order object
     * @return array The order amounts
     */
    public function calculateOrderPrice($order, $savePrices=true) {
    	if ($order == null) {
    		return false;	
    	}
    	
    	$orderBasket = $order->get('basket');
    	$shop = $order->getOne('Shop');
    	$modx =& $this->modx;
    	
    	$amounts = array();
    	$amounts['totalProductsPriceIn'] = 0;
    	$amounts['totalProductsPriceEx'] = 0;
    	$amounts['totalShippingPriceEx'] = 0;
    	$amounts['totalWeight'] = 0;
    	$highestTax = null;
    	
    	$this->fireEvent('vcEventCalculateOrder', 'before', array(
			'vcAction' => 'process',
			'shop' => $shop,
			'order' => $order
		));
    	
		if (is_array($orderBasket) && sizeof($orderBasket) > 0) {
	    	foreach($orderBasket as $product) {
	    		$productObject = $this->modx->getObject('vcProduct', $product['id']);
	    		
	    		if ($productObject == null) {
	    			continue;	
	    		}
	    		
	    		// Get the tax category
	    		$taxCategory = $productObject->getOne('TaxCategory'); 
	    		
	    		if ($highestTax == null) {
	    			$highestTax = $taxCategory;	
	    		} elseif ($highestTax->get('pricechange') < $taxCategory->get('pricechange')) {
	    			$highestTax = $taxCategory;	
	    		}
	    		
	    		$productPrice = $this->calculateProductPrice($product, true);
	    		$shippingPrice = $productObject->get('shippingprice');
	    		$amounts['totalProductsPriceIn'] += ($productPrice['in'] * $product['quantity']);
	    		$amounts['totalProductsPriceEx'] += ($productPrice['ex'] * $product['quantity']);
	    		$amounts['totalShippingPriceEx'] += ($shippingPrice * $product['quantity']);
	    		$amounts['totalWeight'] += ($productObject->get('weight') * $product['quantity']);
	    	}
	    	
	    	// Format totals
	    	$amounts['totalProductsPriceIn'] = number_format($amounts['totalProductsPriceIn'], 2, '.', '');
	    	$amounts['totalProductsPriceEx'] = number_format($amounts['totalProductsPriceEx'], 2, '.', '');
	    	$amounts['totalShippingPriceEx'] = number_format($amounts['totalShippingPriceEx'], 2, '.', '');
	    		    	
	    	// Calculate shipping taxes
	    	$taxAmount = ($amounts['totalShippingPriceEx'] / 100) * $highestTax->get('pricechange');
	    	
	    	if ((int) $this->getShopSetting('calculateShippingTaxes', $shop->get('id')) == 1) {
	    		$amounts['totalShippingPriceIn'] = number_format(($amounts['totalShippingPriceEx'] + $taxAmount), 2, '.', '');
	    	} else {
	    		$amounts['totalShippingPriceIn'] = $amounts['totalShippingPriceEx'];
	    	}
	    	
	    	// Get payment method costs
	    	if ($order->get('shippingid') != 0) {
		    	$shippingModule = $order->getOne('ShippingModule');
		    	if ($shippingModule != null) {
		    		// Get the main controller file
		    		$controller = $this->config['corePath'].'modules/shipping/'.$shippingModule->get('controller');
		    		if (is_file($controller)) {
		    			$vcAction = 'calculateOrderAmount';
		    			$returnValue = include($controller);	
		    			
		    			// Merge current shipping data with shipping data returned from module
		    			if (isset($returnValue) && is_array($returnValue)) {
		    				if (!is_array($order->get('shippingdata'))) {
		    					$order->set('shippingdata', array());
		    				}
		    				$order->set('shippingdata', array_merge($order->get('shippingdata'), $returnValue));
		    			}
		    			
		    			unset($returnValue);
		    			if (isset($module)) {
		    				unset($module);	
		    			}
		    		}
		    	}	
	    	}
	    	
	    	// Get payment method costs
	    	if ($order->get('paymentid') != 0) {
		    	$paymentModule = $order->getOne('PaymentModule');
		    	if ($paymentModule != null) {
		    		$amounts['paymentCostsEx'] = 0;
			    	$amounts['paymentCostsIn'] = 0;
			    	$amounts['paymentData'] = array();
			    	
		    		// Get the main controller file
		    		$controller = $this->config['corePath'].'modules/payment/'.$paymentModule->get('controller');
		    		if (is_file($controller)) {
		    			$vcAction = 'calculateOrderAmount';
		    			$returnValue = include($controller);	
		    			
		    			// Merge current payment data with payment data returned from module
		    			if (isset($returnValue) && is_array($returnValue)) {
		    				if (!is_array($order->get('paymentdata'))) {
		    					$order->set('paymentdata', array());
		    				}
		    				$order->set('paymentdata', array_merge($order->get('paymentdata'), $returnValue));
		    			}
		    			
		    			unset($returnValue);
		    			if (isset($module)) {
		    				unset($module);	
		    			}
		    		}
		    	}	
	    	}
	    	    
			$order->set('totalweight', $amounts['totalWeight']);	
			$order->set('totalproductamountex', $amounts['totalProductsPriceEx']);
			$order->set('totalproductamountin', $amounts['totalProductsPriceIn']);
			$order->set('totalorderamountin', ($amounts['totalProductsPriceIn'] + $order->get('shippingcostsin') + $order->get('paymentcostsin')));
			$order->set('totalorderamountex', ($amounts['totalProductsPriceEx'] + $order->get('shippingcostsex') + $order->get('paymentcostsex')));
		} else {
			$order->set('totalweight', 0);	
			$order->set('totalproductamountex', 0);
			$order->set('totalproductamountin', 0);
			$order->set('totalorderamountin', 0);
			$order->set('totalorderamountex', 0);
			$order->set('paymentcostsex', 0);
			$order->set('paymentcostsin', 0);
			$order->set('shippingcostsin', 0);
			$order->set('shippingcostsex', 0);
		}

    	if ($savePrices) {
    		$order->save();
    	}
    	
    	$this->fireEvent('vcEventCalculateOrder', 'after', array(
			'vcAction' => 'process',
			'shop' => $shop,
			'order' => $order
		));
		
    	return $order;
    }
    
    /**
     * calculateProductPrice
     * Calculate product price
     *
     * @access public
     * @param xPDO/array $product The xPDO product object or product array
     * @param bool $asArray Return multidimensional array containing more info
     * @return array The product price (from the cheapest category)
     */
    public function calculateProductPrice($product, $asArray=false) {
    	$quantity = null;
    	if (is_array($product) && isset($product['quantity'])) {
    		$quantity = $product['quantity'];
    	}
    	
    	if (is_array($product) && isset($product['id'])) {
    		$product = $this->modx->getObject('vcProduct', $product['id']);
    	}
    	
    	if ($product == null) {
    		return false;	
    	}
    	
    	if ($asArray) {
    		$productArray = array();
    	}	

    	$categoryPrices = array();
    	
    	// Get all links to categories for this product
    	$query = $this->modx->newQuery('vcProductCategory', array(
    		'productid' => $product->get('id')
    	));
    	$productCategories = $this->modx->getCollection('vcProductCategory', $query);
    	
    	$this->fireEvent('vcCalculateProductPrice', 'onBeforeCalculate', array(
    		'product' => &$product,
    		'asArray' => $asArray
    	));
    	
    	$productHasTier = false;
    	// Check if the product has a tier price, if it has we don't need to check the categories since it's overridden
    	$tierPrice = $product->get('tierprice');
		if ($tierPrice != '' && $quantity != null) {
			if (is_array($tierPrice) && !empty($tierPrice)) {
				$productHasTier = true;
				foreach($tierPrice as $tier) {
					if ($quantity >= $tier['quantity'] && $tier['quantity'] != 0) {
						$activeTier = $tier;	
					}
				}
			}
		}

		if (!$productHasTier) {
			$tierArray = array();	
		}
		
    	foreach($productCategories as $productCategory) {
    		$category = $productCategory->getOne('Category');
    		if ($category->active != 1) {
    			continue;	
    		}
    		
    		// If there's no active tier, it's possible the product should inherit one from the categories. Since we should
    		// get the cheapest possible one we will create an array of tier prices here.
    		if (!$productHasTier) {
    			$tierPrice = $category->get('tierprice');	
    			if (is_array($tierPrice) && !empty($tierPrice)) {
    				foreach($tierPrice as $tier) {
	    				if ($quantity >= $tier['quantity'] && $tier['quantity'] != 0) {
				    		$tierArray[] = array(
				    			'tier' => $tier,
				    			'category' => $category
				    		);
	    				}
    				}
    			}
    		}
    		
    		// Check if it's by amount
    		if ($category->get('pricechange') != '' && ($category->get('pricepercent') == '' || $category->get('pricepercent') == '0')) {
    			$priceChange = floatval($category->get('pricechange'));
    			
    			if ($priceChange > 0) {
    				$categoryPrices[$category->get('id')] = $product->get('price') + $category->get('pricechange');
    			} else {
    				$categoryPrices[$category->get('id')] = $product->get('price') - abs($category->get('pricechange'));
    			}
    		} else {
    			// Or by percentage	
    			$percentage = floatval($category->get('pricechange'));
    			
    			if ($percentage > 0) {
    				$percentagePrice = ($product->get('price') / 100) * $percentage;
    				$categoryPrices[$category->get('id')] = $product->get('price') + $percentagePrice;	
    			} else {
    				$percentagePrice = ($product->get('price') / 100) * abs($percentage);
    				$categoryPrices[$category->get('id')] = $product->get('price') - $percentagePrice;
    			}
    		}
    	}
    	    	
    	// Get the cheapest price
    	$cheapest = min($categoryPrices);
    	
    	if (!$productHasTier && isset($tierArray) && sizeof($tierArray) > 0) {
    		$mostTierDiscount = 0;
    		foreach($tierArray as $categoryTier) {
    			// Calculate the discount from this tier
	    		if ($categoryTier['tier']['modifier'] == 0) {
	    			$tierDiscount = $categoryTier['tier']['amount'];
	    		} else {
	    			// Calculate percentage amount
	    			$tierDiscount = ($cheapest / 100) * $categoryTier['tier']['amount'];
	    		}

	    		if ($tierDiscount > $mostTierDiscount) {
	    			$mostTierDiscount = $tierDiscount;
	    			$activeTier = $categoryTier['tier'];	
	    		}
    		}
    	}
    	    	
    	// If there's an active tier, we should make it active
    	if (isset($activeTier) && $quantity != null) {
    		if ($activeTier['modifier'] == 0) {
    			$cheapest = $cheapest - $activeTier['amount'];
    		} else {
    			// Calculate percentage amount
    			$percentageAmount = ($cheapest / 100) * $activeTier['amount'];
    			$cheapest = $cheapest - $percentageAmount;
    		}
    	}
    	
    	$product->set('price', $cheapest);
    	
    	$this->fireEvent('vcCalculateProductPrice', 'onAfterCalculate', array(
    		'product' => &$product,
    		'asArray' => $asArray
    	));

    	// Get the tax category
    	$taxCategory = $product->getOne('TaxCategory');
    	$taxAmount = ($product->get('price') / 100) * abs($taxCategory->get('pricechange'));
    	$totalAmount = number_format(($product->get('price') + $taxAmount), 2, '.', '');
    	
    	if ($asArray) {
    		$productArray['ex'] = $product->get('price');
    		$productArray['in'] = $totalAmount;
    		return $productArray;
    	}
    	
    	return $totalAmount;
    }
    
    /**
     * generateOrderNumber
     * Generate the next order number based on the template configured in the shop
     *
     * @access public
     * @param bool $updateLastOrderNumber Whether to set this as the "last" generated bill number
     * @return string The generated order number
     */
    public function generateOrderNumber($updateLastOrderNumber=true, $config=array()) {
    	if (isset($config['shopId'])) {
    		$shop = $this->modx->getObject('vcShop', (int) $config['shopId']);
    	} else {
    		$shop = $this->shop;	
    	}
    	
    	$shopConfig = $this->shop->get('config');
    	
    	$currentOrderNumber = $shopConfig['currentOrderNumber'];
    	$nextOrderNumber = $currentOrderNumber + 1;
    	
    	$orderNumber = '';
    	for($i = 0; $i < ($shopConfig['orderNumberLength'] - strlen($nextOrderNumber)); $i++) {
    		$orderNumber = $orderNumber.'0';
    	}
    	$orderNumber = $orderNumber.$nextOrderNumber;
    	
    	$output = $shopConfig['orderNumberFormat'];
    	$output = str_replace('[[+year]]', date('Y'), $output);
    	$output = str_replace('[[+ordernumber]]', $orderNumber, $output);
    	$output = str_replace('[[+orderNumber]]', $orderNumber, $output);
    	
    	if ($updateLastOrderNumber) {
    		$shopConfig['currentOrderNumber'] += 1;
    		$shop->set('config', $shopConfig);
    		$shop->save();
    	}
    	
    	return $output;
    }
    
    /**
     * cleanExt
     * Removes id="ext-gen123" random ID's and replaces tags with xHTML compliant tags
     *
     * @access public
     * @param string $content The HTML contents of a Ext HtmlEditor field
     * @return string The stripped down version of the string 
     */
    public function cleanExt($content) {
    	$content = preg_replace('%id\=\"ext-gen.+?\"%', '', $content);
    	
    	$input = array('<br>', '<hr>');
    	$output = array('<br />', '<hr />');
    	$content = str_replace($input, $output, $content);
    	
    	$content = strip_tags($content, '<a><b><i><u><h1><h2><h3><h4><h5><p><pre><div><br>');
    	
    	return $content;
    }
    
    /**
     * Log an error into VisionCart error log
     *
     * @access public
     * @param string $message The log message
     * @param string $file The file where the error occured
     * @param string $line The line where the error occured
     * @param int $level 1 = Critical, 2 = notice
     */
    function logError($message, $file='', $line=0, $level=1) {
    	static $logHandle;
    	
    	if ($this->logLevel == 0 || $this->logLevel == 1 && $level == 2) {
    		return false;	
    	}
    	
    	if (!isset($logHandle)) {
    		$logHandle = fopen($this->config['assetsBasepath'].'mgr/log/error.log', 'a+');	
    		
    		if (!$logHandle) {
    			modX::log(0, 'VisionCart: Cannot open logfile for writing');
    		}
    	}
    	
    	if ($logHandle) {
    		fwrite($logHandle, '['.date('r').'] - ('.$level.') - '.$message.'. FILE: "'.$file.'" @ LINE: '.$line."\n");
    	}
    }
    
    function _cmp($a, $b) {
	    if ($a[$this->arrayKey] == $b[$this->arrayKey]) {
	        return 0;
	    }
	    return ($a[$this->arrayKey] < $b[$this->arrayKey]) ? -1 : 1;
    }
    
    function arrayMultiSort($array, $key) {
    	$this->arrayKey = $key;
    	
    	usort($array, array($this, '_cmp'));
    	
    	return $array;
    }
    
    /**
     * Example function
     * This is how we should place comments and functions
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function exampleFunction($name, $properties=array()) {
    	
    }
    
    public function __destruct() {
    	unset($this->config, $this->router, $this->configFiles, $this->shop, $this->category, $this->product);
    }
}