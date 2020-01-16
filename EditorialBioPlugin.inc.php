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
			HookRegistry::register('TemplateManager::fetch', array($this, 'templateFetchCallback'));
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
	 * Hook callback: add biography link example
	 * @param $hookName string The name of the invoked hook
	 * @param $params array Hook parameters
	 */
	public function templateFetchCallback($hookName, $params) {
		$request = $this->getRequest();
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();

		$resourceName = $params[1];
		if ($resourceName === 'controllers/grid/gridRow.tpl') {
			$templateMgr = $params[0];
			// fetch the gridrow from the template
			if (method_exists($templateMgr, 'getTemplateVars')) {
				// Smarty 3
				$row = $templateMgr->getTemplateVars('row');
			} else {
				// Smarty 2
				$row = $templateMgr->get_template_vars('row');
			}
			$data = $row ? $row->getData() : array();
			// Is this a User grid?
			if (is_a($data, 'User')) {
				// userid from the grid
				$userid = $data->getId();
				// Is data present, and is the user eligible?
				if ($row->hasActions() && $this->isEditorWithBio($userid)) {
					import('lib.pkp.classes.linkAction.request.OpenWindowAction');
					$row->addAction(new LinkAction(
						'plugins.generic.editorialBio.bioLink',
						new OpenWindowAction(
							$dispatcher->url($request, ROUTE_PAGE, null, 'about', 'editorialTeamBio', $userid)
						),
						__('about.editorialTeam.biography'),
						null
					));
				}
			}
		}
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

	/**
	 * Check if a user (by userid) is an editor with a biography
	 * @param $userid int User Id
	 * @return PKPUser
	 */
	public function isEditorWithBio($userid) {
		$request = $this->getRequest();
		$userdao = DAORegistry::getDAO('UserDAO');
		$editor = $userdao->getById($userid);
		if ($editor) {
			$context = $request->getContext();
			$contextId = $context ? $context->getId() : CONTEXT_SITE;
			if ($editor->hasRole([ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR], $contextId) && $editor->getLocalizedData('biography')) {
				return $editor;
			}
		}
		return false;
	}
}
