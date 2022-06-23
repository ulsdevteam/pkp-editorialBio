{**
 * templates/frontend/pages/aboutThisPublishingSystem.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view details about an Editor
 *
 * @uses $editor User The editor with a bio to show
 *}
{assign var="pageTitle" value=$editor->getFullname()}
{include file="frontend/components/header.tpl" pageTitleTranslated="$pageTitle"}

<div class="page page_about_editorial_team_bio">
	{include file="frontend/components/breadcrumbs.tpl" currentTitle="$pageTitle"}
	<h1>
		{translate key="about.editorialTeam"}
	</h1>
	<h2>
		{$pageTitle|strip_unsafe_html}
	</h2>

	<div class="editorialTeamBioContent">
		<img src="{$baseUrl}/{$publicfiles}/{$editor->_data['profileImage']['uploadName']}?{""|uniqid}" alt="{translate key="user.profile.form.profileImage"}" />
		{$editor->getLocalizedData('biography')|strip_unsafe_html}
		{$editor->orcid|strip_unsafe_html}
	</div>

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}

