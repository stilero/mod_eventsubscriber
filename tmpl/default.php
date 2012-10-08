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
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of default.
 * 
 * Module_Event_Subscriber is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Module_Event_Subscriber is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Module_Event_Subscriber.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div id="eventsubscriber" class="cssclass<?php echo $params->get('moduleclass_sfx'); ?>">
    <p>
    <?php print $params->get('introtext'); ?>
    </p>
    <?php
    if($subscriptions != null){
        require(JModuleHelper::getLayoutPath('mod_eventsubscriber','subscriptions'));
    }
    if(modEventSubscriperHelper::isViewingCategory()){
        $subscriptionLayout = 'subscribe';
        if(modEventSubscriperHelper::isSubscribing(modEventSubscriperHelper::getCurrentCategoryId())){
            $subscriptionLayout = 'unsubscribe';
        } 
        require(JModuleHelper::getLayoutPath('mod_eventsubscriber',$subscriptionLayout));
    }
    ?>
</div>