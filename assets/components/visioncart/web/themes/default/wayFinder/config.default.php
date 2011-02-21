<?php
/**
 * wayFinder default configuration
 * This file represents the default configuration style for wayFinder.
 * @notice Globalised variables from the main included functions are $shopId, $topic, $key and $config
 * @notice Use the $config = array(); to block duplicate output or adding to previously loaded arrays 
 * 
 * @package visioncart
 * 
 */

$params = array();

/**
 *	Settings
 **/

$params['showProducts'] = 1;

/**
 *	Category
 **/
 
$params['wrapperTpl'] = '@CODE:<ul>
[[+innerChunk]]
</ul>';

$params['categoryTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['firstCategoryTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['lastCategoryTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['activeCategoryTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['activeParentTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['categoryClass'] = 'item-category';
$params['activeCategoryClass'] = 'item-category-active';
$params['activeParentClass'] = 'item-category-child-active';
$params['firstCategoryClass'] = 'item-category-first';
$params['lastCategoryClass'] = 'item-category-last';

/**
 *	Product
 **/
 
$params['productTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['activeProductTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['firstProductTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['lastProductTpl'] = '@CODE:<li class="[[+item.classes:default=``]]">
<a href="[[+item.url]]" title="[[+item.name]]">[[+item.name]] ([[+item.id]])</a>[[+children]]
</li>';

$params['productClass'] = 'item-product';
$params['activeProductClass'] = 'item-product-active';
$params['firstProductClass'] = 'item-product-first';
$params['lastProductClass'] = 'item-product-last';