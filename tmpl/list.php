<?php
/**
 * Module_Event_Subscriber
 *
 * @version  1.0
 * @author Daniel Eliasson Stilero Webdesign http://www.stilero.com
 * @copyright  (C) 2012-okt-06 Stilero Webdesign, Stilero AB
 * @category Module Layout
 * @license	GPLv2
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 
$categories = modEventSubscriperHelper::getCategories();
?>
<p><?php print $params->get('introtext'); ?></p>
<p>
    <ul class="cssclass<?php echo $params->get('moduleclass_sfx'); ?>">
        <?php $link = 'index.php?option=com_rseventspro&Itemid='.JRequest::getInt('Itemid').'&category=';?>
        <?php foreach ($categories as $category) : ?>
        <?php 
        $link = rseventsproHelper::route('index.php?option=com_rseventspro&category='.rseventsproHelper::sef($category->category,$category->name),true,$itemid);
        ?>
        <li class="subscription<?php echo $params->get('moduleclass_sfx'); ?>">
            <a href="<?php echo JRoute::_($link.$category->category.':'.$category->name); ?>" 
               class="subscription<?php echo $params->get('moduleclass_sfx'); ?>">
                <span class="label"><?php echo $category->name; ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</p>
