<?php
/**
 * @package visioncart
 */

switch($scriptProperties['vcAction']) {
	case 'getParams':
		// Parameters for the tab, you can set ANY valid Ext tab parameters for this array
		$tabParams['active'] = false; // Disable plugin
		$tabParams['showTab'] = true; // true for a GUI, false for just a plugin without a GUI
		$tabParams['event'] = 'onBeforeSave'; // onBeforeSave or onAfterSave
		$tabParams['title'] = 'Nog een test';
		$tabParams['layout'] = 'form';
		$tabParams['autoHeight'] = true;
		
		return json_encode($tabParams);
		break;
	case 'view':
		// Content should be returned, like a normal snippet.
		$productId = $scriptProperties['productId'];
		
		// Create an EXT field in PHP (for ease of JSON encoding)
		$itemArray = array(
			'name' => 'testfieldset2',
			'xtype' => 'textarea',
			'fieldLabel' => 'Test veld in fieldset PHP',
			'anchor' => '100%',
			'value' => $modx->visioncart->getProductCustomField($productId, 'testfieldset2') // Get a custom field value from the database
		);
		
		ob_start();
		?>
		<script type="text/javascript">
			var fields = [
				{
					xtype: 'fieldset',
					title: 'Test fieldset',
					items: [
						<?php echo json_encode($itemArray); ?>
					]
				},
				{
					name: 'testfield',
					xtype: 'textfield',
					fieldLabel: 'Nog iets'	
				}
			];
			vcCore.pageClass.addFields(fields, <?php echo $_REQUEST['tab']; ?>);
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
		$optionArray['description'] = 'Test gek jonge';
		$_REQUEST['formData'] = json_encode($optionArray);
		$modx->visioncart->setProductCustomField($productId, 'testfieldset2', $formData['testfieldset2']); // Set a custom field value from the database
		break;	
}