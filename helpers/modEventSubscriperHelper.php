<?php
/**
 * Description of Module_Event_Subscriber
 *
 * @version  1.0
 * @author Daniel Eliasson Stilero Webdesign http://www.stilero.com
 * @copyright  (C) 2012-okt-07 Stilero Webdesign, Stilero AB
 * @category Module Helper
 * @license	GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of modEventSubscriperHelper.
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
defined('_JEXEC') or die('Restricted access'); 

class modEventSubscriperHelper{
    
    static function &getList(&$params){
        $db =& JFactory::getDBO();
        $count = (int) $params->get('count', 5);
        $query = modEventSubscriperHelper::_buildQuery($count);
        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }
    
    static function &getSubscriptions(){
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__eventsubscriber_subsctiptions');
        $useridKey = $db->nameQuote('userid');
        $user =& JFactory::getUser();
        $useridVal = $db->quote($user->id);
        $query = ' SELECT * FROM ' . $table
        . ' WHERE ' . $useridKey . ' = ' . $useridVal;
        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }
    
    static function isSubscribing($catid = null){
        if($catid === null || !is_int($catid)){
            return FALSE;
        }
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__eventsubscriber_subsctiptions');
        $useridKey = $db->nameQuote('userid');
        $user =& JFactory::getUser();
        $useridVal = $db->quote($user->id);
        $catKey = $db->nameQuote('category');
        $catVal = $db->quote((int)$catid);
        $query = ' SELECT * FROM ' . $table
            .' WHERE '.$useridKey.' = '.$useridVal
            .' AND '.$catKey.' = '.$catVal;
        $db->setQuery($query);
        $result = $db->loadResult();
        if($result === null){
            return false;
        }
        return true;
    }
    
    static function isViewingCategory(){
        $option = JRequest::getCmd('option', null);
        $category = JRequest::getVar('category', false);
        if($option != 'com_rseventspro'){
            return FALSE;
        }
        if(!$category){
            return FALSE;
        }
        return TRUE;
    }
    
    static function getCurrentCategoryId(){
        $isViewingCategory = modEventSubscriperHelper::isViewingCategory();
        if(!$isViewingCategory){
            return null;
        }
        $category = JRequest::getVar('category', false);
        if($category){
            $catid = explode(':', $category);
            return (int)$catid[0];
        }
        return null;
    }
    
    static function _buildQuery($count){
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__module_event_subscriber_table');
        $key = $db->nameQuote('key');
        $val = $db->quote('val');
        $query = ' SELECT * FROM ' . $table
        . ' WHERE ' . $key . ' = ' . $val
        . ' LIMIT ' . $count . '';
        return $query;
    }
    static function catchTask(){
        $task = JRequest::getCmd('mod_eventsubscriber_task');
        $catid = JRequest::getInt('mod_eventsubscriber_catid');
        $user =& JFactory::getUser();
        $userid = (int)$user->id;
        if($catid === null){
            return;
        }
        if($task == 'subscribe'){
            modEventSubscriperHelper::addSubscription($catid, $userid);
            $app =& JFactory::getApplication();
            $app->enqueueMessage(JText::_('Subscription Added'));
        }else if($task == 'unsubscribe'){
            modEventSubscriperHelper::removeSubscription($catid, $userid);
            $app =& JFactory::getApplication();
            $app->enqueueMessage(JText::_('Subscription Removed'));
        }
    }
    
    static function addSubscription($catid, $userid){
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__eventsubscriber_subsctiptions');
        $idKey = $db->nameQuote('id');
        $lastvisitKey = $db->nameQuote('lastvisit');
        $useridKey = $db->nameQuote('userid');
        $useridVal = $db->quote($userid);
        $catKey = $db->nameQuote('category');
        $catVal = $db->quote((int)$catid);
        $query = ' INSERT INTO ' . $table.' ('
                .$idKey.', '
                .$useridKey.', '
                .$catKey.', '
                .$lastvisitKey
                .') VALUES ( '
                .'NULL, '
                .$useridVal.', '
                .$catVal.', '
                .'NULL);';
        $db->setQuery($query);
        $result = $db->query();
        if($result === null){
            return false;
        }
        return true;
    }
    
    static function removeSubscription($catid, $userid){
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__eventsubscriber_subsctiptions');
        $idKey = $db->nameQuote('id');
        $lastvisitKey = $db->nameQuote('lastvisit');
        $useridKey = $db->nameQuote('userid');
        $useridVal = $db->quote($userid);
        $catKey = $db->nameQuote('category');
        $catVal = $db->quote((int)$catid);
        $query = 'DELETE FROM ' . $table
                .' WHERE '
                .$useridKey.' = '.$useridVal
                .' AND '
                .$catKey.' = '.$catVal;
        $db->setQuery($query);
        $result = $db->query();
        if($result === null){
            return false;
        }
        return true;
    }
}
