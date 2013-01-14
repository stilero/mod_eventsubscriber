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
    
    static function getCategoryIdsSubscribed(){
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__eventsubscriber_subsctiptions');
        $useridKey = $db->nameQuote('userid');
        $user =& JFactory::getUser();
        $useridVal = $db->quote($user->id);
        $catKey = $db->nameQuote('category');
        $query = ' SELECT '.$catKey.' FROM ' . $table
        . ' WHERE ' . $useridKey . ' = ' . $useridVal;
        $db->setQuery($query);
        $result = $db->loadResultArray();
        return $result;
    }
    
    static function getCategories(){
        $userid =& JFactory::getUser()->id;
        $db =& JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('a.category, b.name');
        $query->from($db->nameQuote('#__eventsubscriber_subsctiptions').' AS a ');
        $query->leftJoin($db->nameQuote('#__rseventspro_categories').' AS b ON b.id = a.category');
        $query->where('a.userid = '.(int)$userid);
        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }
    
    static function getEventsInCatSinceLastVisit($cat){
        $lastVisit = modEventSubscriperHelper::getLastVisit($cat);
        $db =& JFactory::getDBO();
        $countKey = $db->nameQuote('count');
        $eventTable = $db->nameQuote('#__rseventspro_events');
        $catTable = $db->nameQuote('#__rseventspro_categories');
        $taxTable = $db->nameQuote('#__rseventspro_taxonomy');
        $visitVal = $db->quote($lastVisit);
        $query = "SELECT COUNT(*) AS ".$countKey.","
            ."c.name,"
            ."c.id"
            ." FROM ".$taxTable." t "
            ." INNER JOIN ".$eventTable." e "
            ." ON e.id = t.ide"
            ." INNER JOIN ".$catTable." c "
            ." ON c.id = t.id"
            ." WHERE e.created > ".$visitVal
            ." AND t.type = 'category'"
            ." AND t.id = ".$cat
            ." GROUP BY (c.name)";
        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }
    
    static function getAllEventsSinceLastVisit(){
        $catIds = modEventSubscriperHelper::getCategoryIdsSubscribed();
        if($catIds === null){
            return null;
        }
        $events = array();
        foreach ($catIds as $catId) {
            $newEvents = modEventSubscriperHelper::getEventsInCatSinceLastVisit($catId);
            if(!empty($newEvents)){
                $events = array_merge($events, $newEvents);
            }
        }
        return $events;
    }
    
    static function getEventsIdsFromSubscribedCategories(){
        $categories = modEventSubscriperHelper::getCategoryIdsSubscribed();
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__rseventspro_taxonomy');
        $ideKey = $db->nameQuote('ide');
        $typeKey = $db->nameQuote('type');
        $idKey = $db->nameQuote('id');
        $typeVal = $db->quote('category');
        $query = "SELECT DISTINCT ".$ideKey
                ." FROM ".$table
                ." WHERE ".$typeKey." = ".$typeVal
                ." AND ".$idKey." IN(".implode(',', $categories).");";
        $db->setQuery($query);
        $result = $db->loadResultArray();
        return $result;
    }
    
    static function getLastVisit($catid){
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__eventsubscriber_subsctiptions');
        $useridKey = $db->nameQuote('userid');
        $user =& JFactory::getUser();
        $useridVal = $db->quote($user->id);
        $lastVisitKey = $db->nameQuote('lastvisit');
        $catKey = $db->nameQuote('category');
        $catVal = (int)$catid;
        $query = ' SELECT * FROM ' . $table
                .' WHERE '.$useridKey.' = '.$useridVal
                .' AND '.$catKey.' = '.$catid
                . ' ORDER BY ' . $lastVisitKey . ' DESC LIMIT 0,1;';
        $db->setQuery($query);
        $result = $db->loadObject();
        if($result === null){
            return '0000-00-00 00:00:00';
        }else{
            return $result->lastvisit;
        }
    }
    
    static function setLastVisit(){
        $db =& JFactory::getDBO();
        $table = $db->nameQuote('#__eventsubscriber_subsctiptions');
        $useridKey = $db->nameQuote('userid');
        $user =& JFactory::getUser();
        $useridVal = (int)$user->id;
        $lastVisitKey = $db->nameQuote('lastvisit');
        jimport('joomla.utilities.date');
        $date = new JDate(JRequest::getVar('created', '', 'post'));
        $lastVisitVal = $db->quote($date->toMySQL());
        $catKey = $db->nameQuote('category');
        $catVal = (int)modEventSubscriperHelper::getCurrentCategoryId();
        $query = ' UPDATE ' . $table
                .' SET '.$lastVisitKey.' = '.$lastVisitVal
                .' WHERE '.$useridKey.' = '.$useridVal
                .' AND '.$catKey.' = '.$catVal;
        $db->setQuery($query);
        $db->query();
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
            $app->enqueueMessage(JText::_('MOD_EVENTSUBSCRIBER_SUB_ADDED'));
        }else if($task == 'unsubscribe'){
            modEventSubscriperHelper::removeSubscription($catid, $userid);
            $app =& JFactory::getApplication();
            $app->enqueueMessage(JText::_('MOD_EVENTSUBSCRIBER_SUB_REMOVED'));
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
