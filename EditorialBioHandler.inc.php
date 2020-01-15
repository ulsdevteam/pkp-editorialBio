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

import('classes.handler.Handler');

class EditorialBioHandler extends Handler {

	/**
	 * Handle editorialTeamBio action
	 * @param $args array Arguments array.
	 * @param $request PKPRequest Request object.
	 */
	function editorialTeamBio($args, $request) {
		$userId = (int)$args[0];
		$userdao = DAORegistry::getDAO('UserDAO');
		$editor = $userdao->getById($userId);
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_SITE;
		if ($editor->hasRole([ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR], $contextId) && $editor->getLocalizedData('biography')) {
			// This user is an editor and has a biography
			$plugin = PluginRegistry::getPlugin('generic', 'editorialbioplugin');
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('editor', $editor);
			$templateMgr->display($plugin->getTemplateResource('frontend/pages/aboutEditorialTeamBio.tpl'));
		} else {
			// Don't trust other users biographies
			
		}
	}
}
