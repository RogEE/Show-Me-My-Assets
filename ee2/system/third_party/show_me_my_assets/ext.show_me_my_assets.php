<?php

/*
=====================================================

RogEE "Show Me My Assets!"
an extension for ExpressionEngine 2
by Michael Rog

Contact Michael with questions, feedback, suggestions, bugs, etc.
>> http://rog.ee/show_me_my_assets
>> http://devot-ee.com/add-ons/show-me-my-assets

This extension is compatible with NSM Addon Updater:
>> http://ee-garage.com/nsm-addon-updater

Changelog:
>> http://rog.ee/versions/show_me_my_assets

=====================================================

*/


if (!defined('APP_VER') || !defined('BASEPATH')) { exit('No direct script access allowed'); }

// -----------------------------------------
//	Here goes nothin...
// -----------------------------------------

if (! defined('ROGEE_SMMA_VERSION'))
{
	// get the version from config.php
	require PATH_THIRD.'show_me_my_assets/config.php';
	define('ROGEE_SMMA_VERSION', $config['version']);
}

/**
 * Show Me My Assets class, for ExpressionEngine 2
 *
 * @package RogEE Show Me My Assets
 * @author Michael Rog <michael@michaelrog.com>
 * @copyright 2010 Michael Rog
 * @see http://rog.ee/show_me_my_assets
 */
class Show_me_my_assets_ext
{

	var $settings = array();
    	
	var $name = "RogEE Show Me My Assets" ;
	var $version = ROGEE_SMMA_VERSION ;
	var $description = "Redirects the File Manager CP link to the Assets file browser" ;
	var $settings_exist = "n" ;
	var $docs_url = "http://rog.ee/show_me_my_assets" ;
	
	
	/**
	* ==============================================
	* Constructor
	* ==============================================
	*
	* @param mixed: Settings array or empty string if none exist.
	*/
	function Show_me_my_assets_ext($settings='')
	{
	
		// ---------------------------------------------
		//	Get a local EE object reference
		// ---------------------------------------------
		
		$this->EE =& get_instance();
				
		// ---------------------------------------------
		//	Localize extension info
		// ---------------------------------------------
		
		$this->EE->lang->loadfile('show_me_my_assets');
		$this->name = $this->EE->lang->line('show_me_my_assets_extension_name');
		$this->description = $this->EE->lang->line('show_me_my_assets_extension_description');
	
	} // END Constructor


	/**
	* ==============================================
	* Activate Extension
	* ==============================================
	*
	* Registers the extension into the exp_extensions table
	*
	* @see http://expressionengine.com/user_guide/development/extensions.html#enable
	*
	* @return void
	*
	*/
	function activate_extension()
	{

		// ---------------------------------------------
		//	Register the hooks for EE-side registrations (default EE Member module)
		// ---------------------------------------------
		
		$hook = array(
			'class'		=> __CLASS__,
			'method'	=> 'i_dont_want_no_ee_file_browser',
			'hook'		=> 'cp_menu_array',
			'settings'	=> serialize($this->settings),
			'priority'	=> 1,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		
		$this->EE->db->insert('extensions', $hook);
		
	} // END activate_extension()



	/**
	* ==============================================
	* Update Extension
	* ==============================================
	*
	* Performs any necessary database updates; runs each time the extension page is visited.
	* 
	* @see http://expressionengine.com/user_guide/development/extensions.html#enable
	*
	* @param string: current version
	* @return mixed: void on update / FALSE if none
	*
	*/
	function update_extension($current = '')
	{
	
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		elseif (version_compare($current, $this->version, '<'))
		{
	
			// ---------------------------------------------
			//	Un-register the hooks
			// ---------------------------------------------
			
			$this->EE->db->where('class', __CLASS__);
			$this->EE->db->delete('extensions');
			
			// ---------------------------------------------
			//	Re-register the hooks by running the Activate Extension function
			// ---------------------------------------------
			
			$this->activate_extension();
		
		}
	
	} // END update_extension()



	/**
	* ==============================================
	* Disable Extension
	* ==============================================
	*
	* Disables extension by removing its references from the exp_extensions table.
	*
	* @see http://expressionengine.com/user_guide/development/extensions.html#disable
	*
	* @return void
	*
	*/
	function disable_extension()
	{
		
		// ---------------------------------------------
		//	Un-register the hooks
		// ---------------------------------------------
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
		
	} // END disable_extension()



	/**
	* ==============================================
	* I Dont Want No EE File Browser!
	* ==============================================
	*
	* The magic happens here.
	* Confirms that Assets is installed and that the user has permissions to see it.
	* If so, replaces the File Manager CP link with a link to Assets.
	*
	* @see http://blog.adamfairholm.com/expressionengine-cp-menu-manipulation/
	*
	* @param Array: menu items, from EE
	* @return Array: modified menu items
	*
	*/
	function i_dont_want_no_ee_file_browser($menu)
	{
		
		// ---------------------------------------------
		//	We won't even bother unless we can find Assets in the exp_modules table.
		//	(We need the module_id anyway.)
		// ---------------------------------------------
		
		$this->EE->db->select('module_id')->from('modules')->where('module_name', "Assets")->limit(1);
		$query = $this->EE->db->get();
		
		if ($query->num_rows() > 0)
		{
			
			// ---------------------------------------------
			//	Does this user have access to Assets?
			//	Like Adam says... don't want to tease them.
			// ---------------------------------------------

			$assets_id = $query->row('module_id');
		
			$assigned = $this->EE->session->userdata('assigned_modules');

			if
			(
				$this->EE->cp->allowed_group('can_access_modules') and
				(
					$this->EE->session->userdata('group_id') == 1 or
					(isset($assigned[$assets_id]) and $assigned[$assets_id] == 'yes')
				)
			)
			{
				
				// ---------------------------------------------
				//	Everything checks out. Somebody's gonna get some Pixely, Tonicy Goodness.
				// ---------------------------------------------
				
				$assets_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=assets';
				$menu['content']['files']["file_manager"] = $assets_url;
				
			}
			
		}
		
		// ---------------------------------------------
		//	Returning the modified menu items Array to EE
		// ---------------------------------------------
		
		return $menu;
						
	} // END i_dont_want_no_ee_file_browser()



} // END CLASS

/* End of file ext.show_me_my_assets.php */
/* Location: ./system/expressionengine/third_party/show_me_my_assets/ext.show_me_my_assets.php */