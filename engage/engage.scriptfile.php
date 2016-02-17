<?php
/**
 * Janrain Engage Plugin for Joomla
 *
 * A plugin for Janrain Engage social registration, authentication & sharing service.
 * This file handles installation, uninstallation and updates.
 *
 * Created on June 2012
 *
 * @package engage
 * @author Jeremy Bradbury
 * @author Janrain, Inc. <info@janrain.com>
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Janrain, Inc./Jeremy Bradbury
 * @see http://developers.janrain.com   Janrain Developers Portal:
 */
class plgContentEngageInstallerScript {
    function uninstall($parent) { // destroys the Janrain Engage table
        echo '<p>'. JText::_('Uninstalling: Janrain Engage Plugin for Joomla! 1.6+') . '</p>';
        $db = JFactory::getDBO ();
        $query = "DROP TABLE `#__janrainengage`";
        $db->setQuery ($query);
        $result = $db->query();
        echo '<p>'. JText::_('Uninsatallation Complete!') . '</p>';
        return $result;
    }
    function install($parent) {// created the Janrain Engage table
        echo '<p>'. JText::_('Installing: Janrain Engage Plugin for Joomla! 1.6+') .'</p>';
        // create the janrain engage table
        $db = JFactory::getDBO ();
        $query = "CREATE TABLE `#__janrainengage`(
            id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
            identifier   VARCHAR(255),
            user_id      INT(11),
            profile_name VARCHAR(255),
            provider     VARCHAR(50)
        )";
        $db->setQuery ($query);
        $result = $db->query();
        echo '<p>'. JText::_('Insatallation Complete!') . '</p>';
        return $result;
    }
}