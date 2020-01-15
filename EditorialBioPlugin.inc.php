<?php

/**
 * @file plugins/generic/editorialBio/EditorialBioPlugin.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class EditorialBioPlugin
 * @ingroup plugins_generic_editorialBio
 *
 * @brief EditorialBio plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class EditorialBioPlugin extends GenericPlugin {
	
	/**
	 * @copydoc LazyLoadPlugin::register()
	 */
	function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			// Add a handler to process the biography page
			HookRegistry::register('LoadHandler', array($this, 'callbackLoadHandler'));
			// Add a convenience link to the biography page
			HookRegistry::register('TemplateManager::display', array($this, 'handleTemplateDisplay'));
			}
		return $success;
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.editorialBio.displayName');
	}

	/**
	 * Get a description of the plugin.
	 * @return String
	 */
	function getDescription() {
		return __('plugins.generic.editorialBio.description');
	}

	/**
	 * Hook callback: register output filter to add privacy notice
	 * @see TemplateManager::display()
	 */
	function handleTemplateDisplay($hookName, $args) {
		$templateMgr = $args[0];
		$template = $args[1];
		if ($template === 'frontend/pages/userRegister.tpl') {
			if (method_exists($templateMgr, 'register_outputfilter')) {
				// 3.1.1 and earlier (Smarty 2)
				$templateMgr->register_outputfilter(array($this, 'userSettingsFilter'));
			} else {
				// 3.1.2 and later (Smarty 3)
				$templateMgr->registerFilter('output', array($this, 'userSettingsFilter'));
			}
		}
		return false;
	}

	/**
	 * Output filter adds privacy notice to registration form.
	 * @param $output string
	 * @param $templateMgr TemplateManager
	 * @return $string
	 */
	function userSettingsFilter($output, $templateMgr) {
		if (preg_match('/<form[^>]+id="register"[^>]+>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$match = $matches[0][0];
			$offset = $matches[0][1];
			$newOutput = substr($output, 0, $offset+strlen($match));
			$newOutput .= '<div id="editorialBioLink">'.__('plugins.generic.editorialBio.privacyNotice').'</div>';
			$newOutput .= substr($output, $offset+strlen($match));
			$output = $newOutput;
			if (method_exists($templateMgr, 'unregister_outputfilter')) {
				// 3.1.1 and earlier (Smarty 2)
				$templateMgr->unregister_outputfilter('userSettingsFilter');
			} else {
				// 3.1.2 and later (Smarty 3)
				$templateMgr->unregisterFilter('output', array($this, 'userSettingsFilter'));
			}
		}
		return $output;
	}

	/**
	 * @see PKPComponentRouter::route()
	 */
	public function callbackLoadHandler($hookName, $args) {
		if ($args[0] === "about" && $args[1] === "editorialTeamBio") {
                        define('HANDLER_CLASS', 'EditorialBioHandler');
			$args[0] = "plugins.generic.editorialBio.".HANDLER_CLASS;
			import($args[0]);
			return true;
		}
		return false;
	}

	/**
	 * @see PKPPlugin::getTemplatePath()
	 */
	function getTemplatePath($inCore = false) {
		$templatePath = parent::getTemplatePath($inCore);
		// OJS 3.1.2 and later include the 'templates' directory, but no trailing slash
		$templateDir = 'templates';
		if (strlen($templatePath) >= strlen($templateDir)) {
			if (substr_compare($templatePath, $templateDir, strlen($templatePath) - strlen($templateDir), strlen($templateDir)) === 0) {
				return $templatePath;
			}
		}
		// OJS 3.1.1 and earlier includes a trailing slash to the plugin path
		return $templatePath . $templateDir . DIRECTORY_SEPARATOR;
	}	

}
