<?php
/*
 * A snippet that add's a new Tab to the new/edit product area.
 * The snippet MUST be prefixed with: vcEventProduct so that the script
 * knows this is a snippet to include.
 */ 

switch($vcAction) {
	case 'getParams':
		$returnArray = array();
		
		// Parameters
		$returnArray['active'] = false;		// Whether or not the plugin is active
		$returnArray['showTab'] = true;		// If false then it's just a code plugin, when true it's also visual
		$returnArray['title'] = 'German';	// The title for the tab
		$returnArray['event'] = 'onBeforeSave'; // before or after product save
		
		// You can supply any Ext panel params here
		$returnArray['layout'] = 'form';
		$returnArray['autoHeight'] = true;
		
		return json_encode($returnArray);
		break;
	case 'view':
		// Available here is the productId
		$productId = $scriptProperties['productId'];

        $itemArray = array(
	        array(
	                'name' => 'german_title',
	                'xtype' => 'textfield',
	                'fieldLabel' => 'Title',
	                'anchor' => '100%',
	                'value' => $modx->visioncart->getProductCustomField($productId, 'german_title') ? $modx->visioncart->getProductCustomField($productId, 'german_title') : '' // Get a custom field value from the database
	        ),
	        array(
				'xtype' => 'htmleditor',
				'fieldLabel' => 'Description',
				'name' => 'german_description',
				'value' => $modx->visioncart->getProductCustomField($productId, 'german_description') ? $modx->visioncart->getProductCustomField($productId, 'german_description') : '',
				'rows' => 4,
				'height' => 250,
				'anchor' => '80%',
				'allowBlank' => false,
				'enableFont' => false,
				'fontFamilies' => array('Verdana')
	        )
	    );

		ob_start();
		?>
		<script type="text/javascript">
			var fields = <?php echo json_encode($itemArray); ?>;
			vcCore.pageClass.addFields(fields, <?php echo $_REQUEST['tab']+1; ?>); // Normally this would just be $_REQUEST['tab'] but there's a little bug in the JS that is already fixed in SVN
		</script>
		<?php	
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
		break;
	case 'process':
		// Receive some script parameters
        $productId = $scriptProperties['productId']; // Product ID
        $formData  = $scriptProperties['formData'];  // Complete array of $_REQUEST
        $product  = $scriptProperties['product'];    // Direct reference to xPDO object
        
        $optionArray = json_decode($_REQUEST['formData'], true);
        
        foreach($optionArray as $key => $value) {
        	if (substr($key, 0, 7) == 'german_') {
       		$modx->visioncart->setProductCustomField($productId, $key, $value); // Set a custom field value from the database			
        	}	
        }
		break;	
}