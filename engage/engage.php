<?php
/**
 * Janrain Engage Plugin for Joomla
 *
 * A plugin for Janrain Engage social registration, authentication & sharing service.
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
defined( '_JEXEC' ) or ( 'Restricted access' ); // no direct access allowed to this file
jimport('joomla.plugin.plugin'); // import Joomla! plugin library
class plgContentEngage extends JPlugin {
    protected static $apikey;
    protected static $appid;
    protected static $realm;
    protected static $app_alias;
    protected static $realmscheme;
    protected static $adminurl;
    protected static $providers;
    protected static $rpxbase = 'https://rpxnow.com';
    protected static $plug_path = 'plugins/content/engage';
    protected static $pub_base;
    protected static $socialpub;
    protected static $tokenurl;
    protected static $message;
    protected $loaded;
    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param object $config  The plugin paramters
     * @since 1.6
     */
    public function __construct(&$subject, $config){
        require_once 'show.php'; // include the classes
        $api  = new Janrain_Engage_API(&$subject, $config); // instantiate
        $user = new Janrain_Engage_User(&$subject, $config); // instantiate
        parent::__construct($subject,$config);
        $this->_plugin =& JPluginHelper::getPlugin( 'content', 'engage' );
        $this->_params = new JRegistry( $this->_plugin->params );
        $api->lookupRp(); // if no app id is set, grab Engage settings
        if(!empty($_POST['token'])) { // token posted?
            if($_GET['janrain_engage']=='login') { // login flag set?
                $token = JRequest::getVar('token', false, 'post');  //grab the token
                $result = $user->authenticate($token);
            }
            if($_GET['janrain_engage']=='add') { // register flag set?
                $token = JRequest::getVar('token', false, 'post');  //grab the token
                $result = $user->add_engage_identifier($token);
            }
        }
    }
    /**
     * replaces script tag with share button in article content
     * @param  string $content  Markup to replace script tag with
     * @return string|false
     */
    private function updateShareContent($content){
        $regex = '/{janrainengage share}/i';
        $replace = EngageShow::shareButton();
        return preg_replace($regex, $replace, $content);
    }
    /**
     * replaces script tag with auth buttons in article content
     * @param string $content Markup to replace script tag with
     * @param string $size    Button size: 'large' for 32px, 'small' for 16px
     * @return string|false
     */
    private function updateLoginContent($content,$size){
        $regex = '/{janrainengage auth}/i';
        $replace = EngageShow::buttons($size);
        return preg_replace($regex, $replace, $content);
    }
    /**
     * hook fires once before the page is renederd accepts nothing, returns nothing
     * outputs login script and style tags to <head>
     */
    function onBeforeRender(){
        EngageShow::Js();
        EngageShow::Css();
    }
    /**
     * hook fires on each article load (mutliple per page) allowing content to be modified
     * and preforms operations on content before it loads
     * @param:  string  The context of the content being passed to the plugin.
     * @param:  mixed   An object with a "text" property or the string to be cloaked.
     * @param:  array   Additional parameters.
     * @param:  int     Optional page number. Unused. Defaults to zero.
     * @return: boolean Always true.
     */
    function onContentPrepare($context,&$article,&$params,$limitstart){
        $auth_btn = $this->params->get('auth_btn');
        $share_btn = $this->params->get('share_btn');
        $size = $this->params->get('auth_size');
	    if ($share_btn == '1') { // insert share buttons into content?
	            $article->text = $this->updateShareContent($article->text);
	        }
	    if ($auth_btn == '2') { // insert auth buttons into content?
	            $article->text = $this->updateLoginContent($article->text,$size);
	    }
	    return true;
    }
}
