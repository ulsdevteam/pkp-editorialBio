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
use APP\handler\Handler;
use APP\template\TemplateManager;
use PKP\plugins\PluginRegistry;

class EditorialBioHandler extends Handler {

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
		$plugin = PluginRegistry::getPlugin('generic', 'editorialbioplugin');
		$editor = $plugin->isEditorWithBio($userId);
		$orcidProfile = PluginRegistry::getPlugin('generic', 'orcidprofileplugin');
		$orcidIcon = $orcidProfile->getIcon();
		$profileImage = $editor->getData('profileImage');
		$profileImageUpload = $profileImage['uploadName'];
		if ($editor) {
			// This user is an editor and has a biography
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('templateEditor', $editor);
			$publicfiles = Config::getVar('files', 'public_files_dir') . '/site';
			$templateMgr->assign('publicfiles', $publicfiles);
			$templateMgr->assign('profileImage', $profileImage);
			$templateMgr->assign('profileImageUpload', $profileImageUpload);
			$templateMgr->assign('orcidIcon', $orcidIcon);
			$tplName = 'frontend/pages/aboutEditorialTeamBio.tpl';
			$tpl = $plugin->getTemplateResource($tplName);
			$templateMgr->display($tpl);
		} else {
			// Don't trust other users biographies
			$dispatcher = $request->getDispatcher();
			$dispatcher->handle404();
		}
	}
}
