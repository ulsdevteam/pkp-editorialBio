(function () {
	if (typeof pkp === 'undefined' || !pkp.registry || !pkp.registry.storeExtend) {
		return;
	}

	pkp.registry.storeExtend('userAccessManager', function (piniaContext) {
		var store = piniaContext.store;

	store.extender.extendFn('getItemActions', function (actions, args) {
		var user = args.user;

		if (!user['editorialBio::hasBio']) {
			return actions;
		}

		var t = pkp.modules.useLocalize.useLocalize().t;
		var url = window.editorialBioBaseUrl + '/about/editorialTeamBio/' + user.id;

		return actions.concat([{
			label: t('plugins.generic.editorialBio.bioLink'),
			name: 'viewBio_' + user.id,
			icon: 'UserProfile',
			url: url,
		}]);
	});
	});
})();
