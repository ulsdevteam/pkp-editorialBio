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
namespace APP\plugins\generic\editorialBio;

use APP\facades\Repo;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\config\Config;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\OpenWindowAction;
use PKP\security\Role;
use PKP\core\PKPApplication;

class EditorialBioPlugin extends GenericPlugin {
	/**
	 * @copydoc LazyLoadPlugin::register()
	 */
	public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			// Add a handler to process the biography page
			Hook::add('LoadHandler', array($this, 'callbackLoadHandler'));
			// Add a convenience link to the biography page
			Hook::add('TemplateManager::fetch', array($this, 'templateFetchCallback'));
			}
		return $success;
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	public function getDisplayName() {
		return __('plugins.generic.editorialBio.displayName');
	}

	/**
	 * Get a description of the plugin.
	 * @return String
	 */
	public function getDescription() {
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
			if (is_a($data, 'User') || is_a($data, 'PKP\user\User')) {
				// userid from the grid
				$userid = $data->getId();
				// Is data present, and is the user eligible?
				if ($row->hasActions() && $this->isEditorWithBio($userid)) {
					$routePage = PKPApplication::ROUTE_PAGE;
					$row->addAction(new LinkAction(
						'plugins.generic.editorialBio.bioLink',
						new OpenWindowAction(
							$dispatcher->url($request, $routePage, null, 'about', 'editorialTeamBio', $userid)
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
	public function getTemplatePath($inCore = false) {
		$templatePath = parent::getTemplatePath($inCore);
		$templateDir = 'templates';
		if (strlen($templatePath) >= strlen($templateDir)) {
			if (substr_compare($templatePath, $templateDir, strlen($templatePath) - strlen($templateDir), strlen($templateDir)) === 0) {
				return $templatePath;
			}
		}
	}	

	/**
	 * Check if a user (by userid) is an editor with a biography
	 * @param $userid int User Id
	 * @return PKPUser|false
	 */
	public function isEditorWithBio($userid) {
		$request = $this->getRequest();
		$editor = Repo::user()->get($userid);
		$editorRoles = [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR];
		if ($editor) {
			$context = $request->getContext();
			$contextId = $context ? $context->getId() : CONTEXT_SITE;
			// Must be an Editor and must have a Biography to be valid
			if ($editor->hasRole($editorRoles, $contextId) && $editor->getLocalizedData('biography')) {
				return $editor;
			}
		}
		return false;
	}

	/**
	 * Return the location of the plugin's CSS file
	 *
	 * @return string
	 */
	public function getStyleSheet() {
		return $this->getPluginPath() . '/css/editorialBio.css';
	}
}

if (!PKP_STRICT_MODE) {
    class_alias('APP\plugins\generic\editorialBio\EditorialBioPlugin', '\EditorialBioPlugin');
}