<?php
/**
 * Janrain Engage Plugin for Joomla
 *
 * Document Long Description
 *
 * PHP4/5
 *
 * Created on June 1, 2012
 *
 * @package engage
 * @author Jeremy Bradbury
 * @author Janrain, Inc. <info@janrain.com>
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Janrain, Inc./Jeremy Bradbury
 * @see http://developers.janrain.com   Janrain Developers Portal:
 */
class plgContentEngageInstallerScript {
    function uninstall($parent) {
        // goodbye
        echo '<p>'. JText::_('Uninstalling: Janrain Engage Plugin for Joomla! 1.6+') . '</p>';
    }
    // TODO: test/refine
    function install($parent) {
        // greeting
        echo '<p>'. JText::_('Installing: Janrain Engage Plugin for Joomla! 1.6+') .'</p>';
        // create the janrain engage table
        $db = &JFactory::getDBO ();
        $query = "CREATE TABLE `#__janrainengage`(
        id INT(11) NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(id),
        identifier VARCHAR(255),
        user_id INT(11),
        profile_name VARCHAR(255),
        provider VARCHAR(50)
        )";
        $db->setQuery ($query);
        return $db->query();
        // TODO: test value and add fail/sucess message
    }
}