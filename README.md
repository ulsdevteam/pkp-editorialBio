# EditorialBio plugin for PKP

This plugin exposes the editorial bio user data as a page, as used to be the case with OJS 2.x.

## Requirements

* OJS 3.x / OMP 3.1 or later
* PHP 7.2 or later

## Installation

Install this as a "generic" plugin. The preferred installation method is through the Plugin Gallery.  To install manually via the filesystem, extract the contents of this archive to an "editorialBio" directory under "plugins/generic" in your OJS root.  To install via Git submodule, target that same directory path: `git submodule add https://github.com/ulsdevteam/pkp-editorialBio plugins/generic/editorialBio` and `git submodule update --init --recursive plugins/generic/editorialBio`.  Run the upgrade script to register this plugin, e.g.: `php tools/upgrade.php upgrade`

## Configuration

No configuration is needed.  Just enable and go!

## Usage

The endpoint [journal_path]/about/editorialTeamBio/[user_id] will respond to display a public editorial bio page, if data is entered in the editorialBio for that user.  To find the path, edit the user's profile to find a reference to the link.

## Author / License

Written by Clinton Graham for the [University of Pittsburgh](http://www.pitt.edu).  Copyright (c) University of Pittsburgh.

Released under a license of GPL v2 or later.
