<?php

/**
 * @file plugins/generic/editorialBio/EditorialBioHandler.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @ingroup plugins_generic_editorialBio
 * @brief Handles controller requests for EditorialBio plugin.
 */

namespace APP\plugins\generic\editorialBio;
use APP\handler\Handler;
use APP\template\TemplateManager;
use PKP\plugins\PluginRegistry;
use PKP\config\Config;

class EditorialBioHandler extends Handler {

	public function __construct(protected EditorialBioPlugin $plugin) {
		parent::__construct();
	}
	/**
	 * Handle editorialTeamBio action
	 * @param $args array Arguments array.
	 * @param $request PKPRequest Request object.
	 */
	public function editorialTeamBio($args, $request) {
		if (preg_match('/^[[:digit:]]+$/', $args[0])) {
			$userId = (int)$args[0];
		} else {
			$userId = 0;
		}
		$editor = $this->plugin->isEditorWithBio($userId);
		$orcidProfile = PluginRegistry::getPlugin('generic', 'orcidprofileplugin');
		if ($orcidProfile) {
			$orcidIcon = $orcidProfile->getIcon();
			$cssRequest = $orcidProfile->getRequest();
		} else {
			$orcidIcon = null;
			$cssRequest = null;
		}
		if ($editor) {
			// This user is an editor and has a biography
			$profileImage = $editor->getData('profileImage');
			$profileImageUpload = $profileImage['uploadName'] ?? null;
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('editor', $editor);
			$publicfiles = Config::getVar('files', 'public_files_dir') . '/site';
			$templateMgr->assign('publicfiles', $publicfiles);
			$templateMgr->assign('profileImage', $profileImage);
			$templateMgr->assign('profileImageUpload', $profileImageUpload);
			$templateMgr->assign('orcidIcon', $orcidIcon);
			if ($orcidProfile) {
				$templateMgr->addStyleSheet(
					'editorialBio',
					$cssRequest->getBaseUrl() . '/' . $this->plugin->getStyleSheet()
				);
			}
			$tplName = 'frontend/pages/aboutEditorialTeamBio.tpl';
			$tpl = $this->plugin->getTemplateResource($tplName);
			$templateMgr->display($tpl);
		} else {
			// Don't trust other users biographies
			$dispatcher = $request->getDispatcher();
			$dispatcher->handle404();
		}
	}
}
