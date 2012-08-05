<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;
/**
 * An example custom profile plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	User.profile
 * @version		1.6
 */
class plgUserRegister extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration'))) {
			return true;
		}
	    // expects something like this:
	     //name=Jeremy+Bradbury&username=Jeremy+Bradbury&email=jeremy@janrain.com
        $form->setFieldAttribute('password1', 'required', false);
        $form->setFieldAttribute('password1', 'password-validation', false);
        $form->setFieldAttribute('password2', 'required', false);
        $form->setFieldAttribute('password2', 'password-validation', false);
        $email = JRequest::getVar('email');
        $form->setValue('name',null,JRequest::getVar('name'));
        $form->setValue('username',null,JRequest::getVar('username'));
        $form->setValue('email1',null,$email);
        $form->setValue('email2',null,$email);
        return true;
	}
}