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
		if (preg_match('/^[[:digit:]]+$/', $args[0])) {
			$userId = (int)$args[0];
		} else {
			$userId = 0;
		}
		$plugin = PluginRegistry::getPlugin('generic', 'editorialbioplugin');
		$editor = $plugin->isEditorWithBio($userId);
		if ($editor) {
			// This user is an editor and has a biography
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('editor', $editor);
			$templateMgr->display($plugin->getTemplateResource('frontend/pages/aboutEditorialTeamBio.tpl'));
		} else {
			// Don't trust other users biographies
			$dispatcher = $request->getDispatcher();
			$dispatcher->handle404();
		}
	}
}
