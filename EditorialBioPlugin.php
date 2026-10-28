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

use APP\core\Application;
use APP\facades\Repo;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\config\Config;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\OpenWindowAction;
use PKP\security\Role;
use PKP\core\PKPApplication;
use PKP\user\User;
use APP\template\TemplateManager;

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
			Hook::add('TemplateManager::display', array($this, 'handleTemplateDisplay'));
			Hook::add('Schema::get::user', array($this, 'addBioFlagToSchema'));
			Hook::add('UserSchema::getProperties::values', array($this, 'addBioFlagSetting'));
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
		$request = Application::get()->getRequest();
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();
		$resourceName = $params[1];
		if ($resourceName === 'controllers/grid/gridRow.tpl') {
			$templateMgr = $params[0];
			$row = $templateMgr->getTemplateVars('row');
			$data = $row ? $row->getData() : array();
			}
			// Is this a User grid?
			if ($data instanceof User) {
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
		return Hook::CONTINUE;
		}

	/**
	 * @see PKPComponentRouter::route()
	 */
	public function callbackLoadHandler($hookName, $args) {
		if ($args[0] === "about" && $args[1] === "editorialTeamBio") {
			$args[3] = new EditorialBioHandler($this);
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
		$request = Application::get()->getRequest();
		$editor = Repo::user()->get($userid);
		$editorRoles = [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR];
		if ($editor) {
			$context = $request->getContext();
			$contextId = $context ? $context->getId() : PKPApplication::CONTEXT_SITE;
			// Must be an Editor and must have a Biography to be valid
			if ($editor->hasRole($editorRoles, $contextId) && $editor->getLocalizedData('biography')) {
				return $editor;
			}
		}
		return false;
	}

	public function addBioFlagToSchema($hookName, $args) {
		$schema = $args[0];
		$schema->properties->{'editorialBio::hasBio'} = (object) [
			'type' => 'boolean',
			'apiSummary' => true,
			'validation' => ['nullable']
		];
		return false;
	}

	public function addBioFlagSetting($hookName, $args) {
		$output =& $args[1];
		$user = $args[2];
		$output['editorialBio::hasBio'] = (bool) $this->isEditorWithBio($user->getId());
		return false;
}
	public function handleTemplateDisplay($hookName, $args) {
		$request = Application::get()->getRequest();
		$templateMgr = $args[0];
		$template = $args[1];
		if ($template === 'management/access.tpl') {
			$context = $request->getContext();
			$contextUrl = $request->getBaseUrl() . ($context ? '/' . $context->getPath() : '');
			$templateMgr->addJavaScript(
				'editorialBioConfig',
				'window.editorialBioBaseUrl = ' . json_encode($contextUrl) . ';',
				['priority' => TemplateManager::STYLE_SEQUENCE_LAST, 'contexts' => 'backend', 'inline' => true]
			);
			$templateMgr->addJavaScript(
				'editorialBioUserAccessManager',
				$request->getBaseUrl() . '/' . $this->getPluginPath() . '/js/userAccessManager.js',
				['priority' => TemplateManager::STYLE_SEQUENCE_LAST, 'contexts' => 'backend']
			);
		}
		return Hook::CONTINUE;
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