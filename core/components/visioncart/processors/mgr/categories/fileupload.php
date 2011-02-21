<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

// Receive REQUEST variables
$targetDir = $modx->getOption('base_path').'assets/components/visioncart/web/images/categories/';
$visionCart = $modx->visioncart;
$sourcePath = $_FILES['image']['tmp_name'];
$fileExtension = strtolower(substr($_FILES['image']['name'], -3));
$shopId = (int) $_REQUEST['shopid'];
$categoryId = (int) $_REQUEST['id'];

// Fetch the current pictures
$category = $modx->getObject('vcCategory', $categoryId);
$categoryConfig = $category->get('config');

if (isset($categoryConfig['categoryThumbnail'])) {
	$currentPicture = $categoryConfig['categoryThumbnail'];
} else {
	$currentPicture = '';	
}

// Check the extension
switch($fileExtension) {
	case 'jpg':
		$sourceImage = imagecreatefromjpeg($sourcePath);
		break;
	case 'gif':
		$sourceImage = imagecreatefromgif($sourcePath);
		break;
	case 'png':
		$sourceImage = imagecreatefrompng($sourcePath);
		break;
}

// Fetch the current thumbnail settings and create all thumbs
$thumbnailArray = $visionCart->getThumbnailSettings($shopId, true);

foreach($thumbnailArray as $thumb) {
	$imageSize = getimagesize($sourcePath);
	list($imageWidth, $imageHeight) = $imageSize;
	
	// Calculations for the new sizes, if cropped and if !cropped
	if ($thumb['crop'] == 'true' || $thumb['crop'] == 'center') {
		$ratioX = $thumb['w'] / $imageWidth;
		$ratioY = $thumb['h'] / $imageHeight;
		
		if ($ratioX < $ratioY) {
			$startX = round(($imageWidth - ($thumb['w'] / $ratioY)) / 2);
			$startY = 0;
			$oldWidth = round($newWidth / $ratioY);
			$oldHeight = $oldHeight;
		} else {
			$startX = 0;
			$startY = round(($imageHeight - ($thumb['h'] / $ratioX)) / 2);
			$imageWidth = $imageWidth;
			$imageHeight = round($thumb['h'] / $ratioX);
		}
		
		$applyWidth = $thumb['w'];
		$applyHeight = $thumb['h'];
	} else {
		$widthScale = 2;
		$heightScale = 2;
		
		$widthScale = $thumb['w'] / $imageWidth;
		$heightScale = $thumb['h'] / $imageHeight;
		
		if ($widthScale < $heightScale) {
			$maxWidth = $thumb['w'];
			$maxHeight = false;	
		} elseif ($widthScale > $heightScale) {
			$maxHeight = $thumb['h'];
			$maxWidth = false;
		} else {
			$maxHeight = $thumb['h'];
			$maxWidth = $thumb['w'];
		}
		
		if ($maxWidth > $maxHeight) {
			$applyWidth = $maxWidth;
			$applyHeight = ($imageHeight * $applyWidth) / $imageWidth;
		} elseif ($maxHeight > $maxWidth) {
			$applyHeight = $maxHeight;
			$applyWidth = ($applyHeight * $imageWidth) / $imageHeight;
		} else {
			$applyWidth = $maxWidth;
			$applyHeight = $maxHeight;
		}
		
		$startX = 0;
		$startY = 0;
	}	

	// Copy the source into the target image, write to disk and destroy the image to free memory
	$target = imagecreatetruecolor($applyWidth, $applyHeight);
	imagecopyresampled($target, $sourceImage, 0, 0, $startX, $startY, $applyWidth, $applyHeight, $imageWidth, $imageHeight);
	imagejpeg($target, $targetDir.$thumb['prefix'].$categoryId.'.jpg', 90);
	imagedestroy($target);
}
imagedestroy($sourceImage);

// Add to current pictures array and save with product
$categoryConfig['categoryThumbnail'] = $categoryId.'.jpg';
$category->set('config', $categoryConfig);
$category->save();

// Hide uploading status in parent
?>
<script type="text/javascript">
	top.vcCore.pageClass.hideUploader();
</script>