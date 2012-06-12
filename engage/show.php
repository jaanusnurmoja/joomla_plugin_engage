<?php
/**
 * Janrain Engage Plugin for Joomla
 *
 * The Show class provides methods assiociated with the presentation layer (HTML/JS/CSS).
 *
 * Created on June 2012
 *
 * @package engage
 * @author Jeremy Bradbury <jeremy@janrain.com>
 * @author Janrain, Inc. <info@janrain.com>
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Janrain, Inc./Jeremy Bradbury
 * @see http://developers.janrain.com   Janrain Developers Portal:
 */
require_once 'user.php';
class EngageShow extends Janrain_Engage_User{
    /**
     * build the sign-in buttons based on selected providers
     * @param  string $size expects "large" or "small"
     * @return string $buttons
     */
    public static function buttons($size = 'large'){
        $iconSize = ($size == 'small') ? '16' : '30';
        $providers = explode(',', parent::$param->get('providers'));
        if (is_array($providers)) {
            $wrap_open = '<a class="janrainEngage" href="#">';
            $wrap_close = '</a><br>';
            foreach ($providers as &$val) { $rpx_buttons .= '<span class="jn-icon jn-size'.$iconSize.' jn-'.$val.'" title="'.htmlentities($val).'"></span>'; }
            $buttons = '<span class="rpx_button">' . $rpx_buttons . '</span>';
            return $wrap_open . $buttons . $wrap_close;
        }
        return false;
    }
    /**
     * build the share buttons based on selected providers
     * @return string $buttons
     */
    public static function shareButton(){
        $social_providers = array_filter(explode(',', parent::$param->getValue('socialpub')));
        foreach ($social_providers as $val) { $rpx_social_icons .= '<div class="jn-icon jn-size16 jn-'.$val.'"></div>'; }
        $buttons = '<div class="rpx_social_icons">' . $rpx_social_icons . '</div>';
        $share = '<div id="janrainEngageShare" class="rpxsocial rpx_tooltip">';
        $share .= '<span class="rpxsharebutton">share</span><div class="rpx_share_tip">Share this on:<br>' . $buttons . '</div></div>';
        //TODO: make a class for this so it can be edited in the css file
        return '<div style="margin-top:35px;" >'.$share.'</div>';
    }
    /**
     * inserts the needed styles and style refrences into the page
     * @return boolean
     */
    public static function Css(){
        $path = parent::$pub_base . parent::$plug_path;
        $doc = &JFactory::getDocument();
        $style =".jn-size30{width:30px;height:30px;background-image:url('$path/jn-icons32.png');}
                 .jn-size16 {width:16px;height:16px;background-image:url('$path/jn-icons16.png');}";
        $doc->addStyleDeclaration($style);
        $doc->addStyleSheet($path.'/stylesheet.css');
        return true;
    }
    /**
     * inserts the needed script and script refrences into the page
     * @return boolean
     */
    public static function Js(){
        $doc = &JFactory::getDocument();
        $pageurl = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $pagetitle = $doc->getTitle();
        $pagedescr = $doc->getMetaData('description');
        $path = parent::$pub_base . parent::$plug_path;
        $tokenurl = parent::$tokenurl;
        $app_alias = parent::$app_alias;
        $message = parent::$message;
        $js = <<<SCRIPT
(function() {
    if (typeof window.janrain !== 'object') window.janrain = {};
    if (typeof window.janrain.settings !== 'object') window.janrain.settings = {};
  
    janrain.settings.tokenUrl = '$tokenurl?janrain_engage=login';
  
    function isReady() { janrain.ready = true; };
    if (document.addEventListener) {
      document.addEventListener("DOMContentLoaded", isReady, false);
    } else {
      window.attachEvent('onload', isReady);
    }
  
    var e = document.createElement('script');
    e.type = 'text/javascript';
    e.id = 'janrainAuthWidget';
  
    if (document.location.protocol === 'https:') {
      e.src = 'https://rpxnow.com/js/lib/$app_alias/engage.js';
    } else {
      e.src = 'http://widget-cdn.rpxnow.com/js/lib/$app_alias/engage.js';
    }
  
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(e, s);
})();
  
(function() {
    if (typeof window.janrain !== 'object') window.janrain = {};
    if (typeof window.janrain.settings !== 'object') window.janrain.settings = {};
    if (typeof window.janrain.settings.share !== 'object') window.janrain.settings.share = {};
    if (typeof window.janrain.settings.packages !== 'object') janrain.settings.packages = ['share'];
    else janrain.settings.packages.push('share');
  
    /* _______________ can edit below this line _______________ */
  
    janrain.settings.share.message = "$message";
    janrain.settings.share.title = "$pagetitle";
    janrain.settings.share.url = "$pageurl";
    janrain.settings.share.description = "$pagedescr";
  
    /* _______________ can edit above this line _______________ */
  
    function isReady() { janrain.ready = true; };
    if (document.addEventListener) {
        document.addEventListener("DOMContentLoaded", isReady, false);
    } else {
        window.attachEvent('onload', isReady);
    }
  
    var e = document.createElement('script');
    e.type = 'text/javascript';
    e.id = 'janrainWidgets';
  
    if (document.location.protocol === 'https:') {
    e.src = 'https://rpxnow.com/js/lib/$app_alias/widget.js';
    } else {
    e.src = 'http://widget-cdn.rpxnow.com/js/lib/$app_alias/widget.js';
    }
  
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(e, s);
})();
SCRIPT;
        $doc->addScriptDeclaration($js);
        return true;
    }
    /**
     * insert auth buttons into login module
     * @param  string $content
     * @param  string $form
     * @param  string $position
     * @return boolean
     */
    public static function updateLoginModule($content, $form='Login Form', $position='pretext'){
        try {
            $module = JModuleHelper::getModule('login',$form);
            $param  = new JRegistry($module->params); // grab existing params
            $content = addslashes($content);
            $param->set($position, $content);
            $paramstring = $param->__toString(); // insert buttons and parse to json
            $db = &JFactory::getDBO();
            $query = "UPDATE `#__modules` SET `params`='$paramstring' WHERE `title`='$form'";
            $db->setQuery($query);
            return $db->query(); // send in the updated params
        } catch (Exception $e) {
            JError::raiseError("Janrain Engage Error","It is likely that the 'Login Form Title' provided in the Engage Settings is incorrect.");
        }

    }
}