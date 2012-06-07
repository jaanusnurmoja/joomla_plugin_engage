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
require_once 'engage.php';
class Janrain_Engage_API extends plgContentEngage {
    const AUTH_INFO = 'auth_info';
    const ACTIVITY  = 'auth_info';
    const LOOKUP_RP = 'lookup_rp';
    const UI_CONFIG = 'ui_config';
    protected static  $param;
    /**
     * build the plugin object and set some params
     */
    public function __construct(&$subject, $config){
        $this->_plugin =& JPluginHelper::getPlugin( 'content', 'engage');
        $this->_params = new JRegistry( $this->_plugin->params );
        self::$param = $this->_params;
        self::$pub_base = str_ireplace("administrator/", "", JURI::base());
        self::$tokenurl = parent::$pub_base;
        $this->initParams();
    }
    /**
     * initalize the auto-generated admin params: Admin Settings -> This Object
     * @return boolean
     */
    public function initParams(){
        self::$apikey      = $this->_params->get('apikey',        false);
        self::$appid       = $this->_params->get('appid',         false);
        self::$app_alias   = $this->_params->get('app_alias',     false);
        self::$realm       = $this->_params->get('realm',         false);
        self::$realmscheme = $this->_params->get('realmscheme',   false);
        self::$adminurl    = $this->_params->get('adminurl',      false);
        self::$socialpub   = $this->_params->get('socialpub',     false);
        self::$providers   = $this->_params->get('providers',     false);
        self::$message     = $this->_params->get('share_msg',     false);
        
        if($this->_params->get('auth_btn')!=$this->_params->get('auth_last')) {
            if ($this->_params->get('auth_btn')=='1') { // insert auth buttons module?
                EngageShow::updateLoginModule(EngageShow::buttons($this->_params->get('auth_size')), $this->_params->get('auth_title'), $this->_params->get('auth_loc'));
            } else { EngageShow::updateLoginModule('', $this->_params->get('auth_title'), $this->_params->get('auth_loc')); }
            $this->_params->set('auth_last',$this->_params->get('auth_btn'));
            $this->setParams();
        }
        return true;
    }
    /**
     * set the auto-generated params: This Object -> Admin Settings
     * @return boolean
     */
    protected function setParams(){
        $this->_params->set('apikey',      self::$apikey);
        $this->_params->set('appid',       self::$appid);
        $this->_params->set('app_alias',   self::$app_alias);
        $this->_params->set('realm',       self::$realm);
        $this->_params->set('realmscheme', self::$realmscheme);
        $this->_params->set('adminurl',    self::$adminurl);
        $this->_params->set('socialpub',   self::$socialpub);
        $this->_params->set('providers',   self::$providers);
        $this->_params->set('providers',   self::$providers);
        $params = $this->_params->__toString(); //parse these to json string
        try {
            $db = &JFactory::getDBO();
            $query = "UPDATE `#__extensions` SET `params`='$params' WHERE `name`='Authentication - Janrain Engage'";
            $db->setQuery($query);
            return $db->query(); // update plugin params
        } catch (Exception $e) {
            JError::raiseError("db_error","{$db->getErrorMsg()}");
            return false;
        }
        return true;
    }
    /**
     * triggers one of 4 Janrain API calls
     * @param  string $method
     * @param  array  $params
     * @param  string $post
     * @return object|boolean
     */
    protected function call($method, $params=false, $post=false){
        if(is_array($params)) {
            if     ($method == self::AUTH_INFO) { $method_fragment = "api/v2/auth_info"; }
            elseif ($method == self::ACTIVITY)  { $method_fragment = "api/v2/activity";  }
            elseif ($method == self::LOOKUP_RP) { $method_fragment = "plugin/lookup_rp"; }
            elseif ($method == self::UI_CONFIG) { $method_fragment = "openid/ui_config"; }
            $params['format'] = 'json';
            $uri = parent::$rpxbase. "/" . $method_fragment;
            $request = new JHttp();
            if ($post)           { $results = $request->post($uri, $params); } // process post request
            else                 { $results = $request->get($uri."?".JURI::buildQuery($params)); } // process get request
            try                  { return json_decode($results->body); } // return request results as object
            catch (Exception $e) { JError::raiseError("bad_data","Could not decode result: {$e->getMessage()}"); }
            return false;
        }
    }
    /**
     * token -> auth info with validation
     * @param string $token
     * @return json string|boolean
     */
    public static function authInfo($token){
        if($token) {
            $params = array('token' => $token, 'apiKey' => self::$apikey);
            if ($auth_info = self::call(self::AUTH_INFO, $params, true)) {
                if (isset($auth_info->stat) && $auth_info->stat =='ok') {
                    return $auth_info;
                }
            }
        }
        return false;
    }
    /**
     * grab the plugin details from Janrain - "Auto-Generated Settings"
     * rebuild the modules
     * @return boolean
     */
    public function lookupRp (){
        if($this->_params->get('refresh')==1 AND $this->_params->get('apikey')!="") { // if refresh and api key is set
            $params = array('apiKey' => parent::$apikey);
            $result = $this->call(self::LOOKUP_RP, $params, true);
            if (isset($result->realm)) {
                self::$appid        = $result->appId;
                self::$realm        = $result->realm;
                self::$app_alias    = str_replace('.rpxnow.com','',self::$realm);
                self::$realmscheme  = $result->realmScheme;
                self::$adminurl     = $result->adminUrl;
                self::$socialpub    = $result->socialPub;
                self::$providers    = $result->signinProviders;
                $this->_params->set('refresh', 0);
                return $this->setParams();
            }
        }
        return false;
    }
}