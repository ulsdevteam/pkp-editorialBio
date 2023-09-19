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

//import('classes.handler.Handler');  //imports need replaced with use statements
use APP\handler\Handler;

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
		if ($editor) {
			// This user is an editor and has a biography
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('editor', $editor);
			// fetch the template across versions
			$tplName = 'frontend/pages/aboutEditorialTeamBio.tpl';
			$tpl = $plugin->getTemplateResource($tplName);
			/*if (method_exists($plugin, 'getTemplateResource')) {
				// 3.1.2 and after
				$tpl = $plugin->getTemplateResource($tplName);
			} else {
				// 3.1.1 and before
				$tpl = $plugin->getTemplatePath() . $tplName;
			}*/

			$templateMgr->display($tpl);
		} else {
			// Don't trust other users biographies
			$dispatcher = $request->getDispatcher();
			$dispatcher->handle404();
		}
	}
}
