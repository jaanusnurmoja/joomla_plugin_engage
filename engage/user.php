<?php
    /**
     * Janrain Engage Plugin for Joomla
     *
     * The User class provides methods assiociated with registering and authenticating Joomla! Users.
     * It also provides methods to attach Janrain Engage provider idenentfiers.
     *
     * Created on June 2012
     *
     * @package engage
     * @author Jeremy Bradbury <jeremy@janrain.com>
     * @author Bryce Hamrick <bryce@janrain.com>
     * @author Janrain, Inc. <info@janrain.com>
     * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
     * @copyright 2012 Janrain, Inc.
     * @see http://developers.janrain.com   Janrain Developers Portal:
     */
    require_once 'api.php';
    // TODO? errors to template
    class Janrain_Engage_User extends Janrain_Engage_API {
        /**
         * lookup identifier
         * @param  string $ident
         * @return int $user_id|NULL|boolean
         */
        protected function lookupIdent($ident){
            try {
                $db = JFactory::getDBO ();
                $query = "SELECT `user_id` FROM `#__janrainengage` WHERE `identifier` = '$ident' LIMIT 1";
                $db->setQuery($query);
                return $db->loadResult();
            } catch (Exception $e) {
                JError::raiseError("db_error","{$db->getErrorMsg()}");
            }
            return false;
        }
        /**
         * lookup user matching user
         * @param  string $ident
         * @return array ($id,$username)|NULL|boolean
         */
        protected function lookupUser($ident){
            try {
                if($exist_user_id = $this->lookupIdent($ident)) {
                    $db = JFactory::getDBO ();
                    $query = "SELECT `id`, `username` FROM `#__users` WHERE `id` = '$exist_user_id' LIMIT 1";
                    $db->setQuery ($query);
                    return $db->loadAssoc();
                }
            } catch (Exception $e) {
                JError::raiseError("db_error","{$db->getErrorMsg()}");
            }
            return false;
        }
        /**
         * registers a new Joomla! user via Janrain Engage
         * @param  object $auth_info
         * @param  object $user
         * @return boolean
         */
        protected function addUser($auth_info,$user){
            $email = $auth_info->profile->verifiedEmail ? $auth_info->profile->verifiedEmail : $auth_info->profile->email;
            if(!isset($email) OR $email =="" OR parent::$param->get('auth_auto')==0){
                $name = $auth_info->profile->name->formatted ? $auth_info->profile->name->formatted : $auth_info->profile->displayName;
                $username = $auth_info->profile->preferredUsername ? $auth_info->profile->preferredUsername : $name;
                $uri = JURI::base();
                $uri .= "?option=com_users&view=registration";
                $uri .= "&name=$name";
                $uri .= "&username=$username";
                $uri .= "&email=$email";
                JFactory::getApplication()->redirect($uri);
                return true;
            }
            $profile_name = $this->profile_name($auth_info);
            $identifier_info = array('identifier' => $auth_info->profile->identifier, 'provider' => $auth_info->profile->providerName, 'profile_name' => $profile_name);
            $reg_info = $this->getReg($auth_info);
			$n = rand(10e16, 10e20);
			$psw = base_convert($n, 10, 36);

            $user->name         = $reg_info[0];
            $user->username     = $reg_info[1];
            $user->email        = $reg_info[2];
            $user->password     = $psw;
            $user->sendEmail    = 1;
            try {
                if($user->save()) { // user saved?
                    $db = JFactory::getDBO ();
                    $query = "INSERT INTO `#__user_usergroup_map`
                    (`user_id`,`group_id`) VALUES
                    ('{$user->id}',2);";
                    $db->setQuery($query);
                    return $db->query(); // add user to "Registered" user group_id = 2
                }
            } catch (Exception $e) { JError::raiseError("db_error","{$db->getErrorMsg()}"); }
            return false;
        }
        /**
         * insert engage user data
         * @param  object $auth_info
         * @param  object $user
         * @return boolean
         */
        protected function addIdent($auth_info,$user){
            try {
                $db = JFactory::getDBO ();
                $query = "INSERT INTO `#__janrainengage`
                (`identifier`,`user_id`,`profile_name`, `provider`) VALUES
                ('{$auth_info->profile->identifier}',
                 '{$user->id}',
                 '{$this->profile_name($auth_info)}',
                 '{$auth_info->profile->providerName}'
                 );";
                $db->setQuery($query);
                return $db->query();
            } catch (Exception $e) { JError::raiseError("db_error","{$db->getErrorMsg()}"); }
            return false;
        }
        /**
         * login user to Joomla!
         * @param  string $username
         * @return boolean
         */
        protected function doLogin($username){
            try {
                JPluginHelper::importPlugin ('user');
                $app = JFactory::getApplication ();
                $options['action'] = 'core.login.site';
                $response['username'] = $username;
                return $app->triggerEvent('onUserLogin',array($response,$options));
            } catch (Exception $e) { JFactory::getApplication()->redirect(JURI::base(), JText::_('Login error, please try again.' )); }
            return false;
        }
        /**
         * grab the profile name
         * @param  object $auth_info
         * @return string $profile_name
         */
        public function profile_name($auth_info){
            $profile_name = '';
            if (isset($auth_info->profile->preferredUsername))   { $profile_name = $auth_info->profile->preferredUsername; }
            elseif (isset($auth_info->profile->email))           { $profile_name = $auth_info->profile->email; }
            elseif (isset($auth_info->profile->displayName))     { $profile_name = $auth_info->profile->displayName; }
            elseif (isset($auth_info->profile->name->formatted)) { $profile_name = $auth_info->profile->name->formatted; }
            else                                                 { $profile_name = $auth_info->profile->providerName; }
            return $profile_name;
        }
        /**
         * get registration info
         * @param  object $auth_info
         * @return array ($name,$username,$email)
         */
        protected function getReg($auth_info){
            
            $email = $auth_info->profile->verifiedEmail ? $auth_info->profile->verifiedEmail : $auth_info->profile->email;
            $username = $email;
            if (isset($auth_info->profile->name->formatted))       { $name = $auth_info->profile->name->formatted; }
            elseif (isset($auth_info->profile->displayName))       { $name = $auth_info->profile->displayName; }
            elseif (isset($auth_info->profile->preferredUsername)) { $name = $username; }
            else                                                   { $name = $email; }
            return array($name,$username,$email);
        }
        /**
         * authentication control flow: see inline comments below.
         * @param string $token
         * @return boolean
         */
        public function authenticate($token){
            $user = JFactory::getUser();
            if ($user->guest != 1) { // user logged in?
                return $this->add_engage_identifier($token); // combine with existing user - must manually place buttons/link on profile page
            }
            if ($auth_info = parent::authInfo($token)) { // auth info valid?
                if ($result = $this->lookupUser($auth_info->profile->identifier) AND !empty($result['username'])) // username returned?
                { return $this->doLogin($result['username']); } // login existing user
                else { // register new user
                    if ($auth_info->profile->identifier) { // identifier exist in token?
                        if($this->addUser($auth_info,$user)) { // user added?
                            if($this->addIdent($auth_info,$user)) { return $this->doLogin($user->username); } // login new user
                            else { JError::raiseError("db_error","Identifier not inserted."); }
                        } else { JFactory::getApplication()->redirect(JURI::base(), JText::_("Email and/or username address already exists. If you've previously registered with us, please login first to add your {$auth_info->profile->providerName} account." )); }
                    } // no identifier in token
                } // lookupUser failed
            } // invalid token
            JError::raiseError("Janran Engage Error",'Could not retrieve provider info. Please try again.');
            return false;
        }
        /**
         * adds Janrain Engage data to an existing user
         * @param  string $token
         * @return boolean
         */
        public function add_engage_identifier($token){
            $user = JFactory::getUser();
            if ($user->guest == 1) {
                JError::raiseError("auth_error",'You must be logged in to perform this action.');
            }
            $auth_info = parent::authInfo($token);
            if ($ident = $auth_info->profile->identifier) { // valid ident?
                $exist_ident = $this->lookupIdent($ident);
                if (isset($exist_ident) && $exist_ident == $user->id) {
                    JFactory::getApplication()->redirect(
                                                         JURI::base(),
                                                         JText::_('This social identifier is already enabled on this account. You might have already added it previously. Did you try to login twice?')
                                                         );
                } elseif (isset($exist_ident) && $exist_ident != $user->id) {
                    JFactory::getApplication()->redirect(JURI::base(), JText::_('This social identifier is associated with another account on this website. The account you logged in with does not match the current user.'));
                } else { return $this->addIdent($auth_info,$user); } // insert user identifier
            }
            JError::raiseError("service_error",'Could not retrieve provider info. Please try again.');
            return false;
        }
    }