### n0tice for WordPress ###
Author: &#198;ndrew Rininsland
Author URI: http://www.aendrew.com
Plugin URI: http://www.n0tice.com
Contributors: aendrew
Tags: n0tice, curation, hyperlocal, news
Requires at least: 2.0.2
Tested up to: 3.3.2
Stable tag: trunk
License: GPLv2

Allows WordPress sites to curate and display content from The Guardian's n0tice community reporting platform. See: http://www.n0tice.com

## Description ##

[n0tice](http://www.n0tice.com) is a hyperlocal, community-driven news, events and classifieds website brought to you by [The Guardian](http://www.guardian.co.uk). 

This plugin is designed to pull content from specific locales and/or n0ticeboards, allowing it to be curated for display on a WordPress blog.

A backend interface allows specific curations to be taken from n0tice, while a widget allows either curations or a raw feed to be displayed in a sidebar.

Plugin created in cooperation with the [Hackney Citizen](http://www.hackneycitizen.co.uk).

For documentation and more information, visit the plugin's [github project](https://github.com/aendrew/n0tice-wp/).

## Installation ##

1. Upload the n0tice directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the n0tice widget to a sidebar via the 'Appearance'->'Widgets' menu.

## Frequently Asked Questions ##

# How do I delete n0tices from curations? #

When editing a curation, simply uncheck the checkbox next to the curation you don't want. It will be removed when you hit "Save curation".

# Where are all of the n0tices going when I click "Save curation"? #

Make sure to tick the checkbox next to each item before clicking "Save curation".

# How do I style the widget? #

Forthcoming: copy n0tice/css/widget.css into a directory named "n0tice", inside of your theme's root directory. This will override the default widget.css file -- feel free to edit that file, it won't get replaced during plugin updates.

# What are some upcoming features? #

* The aforementioned widget theming system.
* Different kinds of widgets: map, calendar, etc.
* RSS support for curations
* Refactored the curation editing page -- instead of one table, will be two: "queue" (i.e., incoming n0tices from searches, etc.) and "published". Additionally, the queue will be live-updating, with searches being "sticky".

# Is this an "official" plugin? #

This plugin has been created independently of [The Guardian](http://www.guardian.co.uk)/[n0tice](http://www.n0tice.com) by [Aendrew Rininsland](http://www.aendrew.com). Please, don't ask either the n0tice or The Guardian for technical support -- instead, direct such requests to n0tice-plugin@aendrew.com.

## Known bugs ##

1. Searching while specifying a noticeboard often lists noticeboards other than the one submitted (I.e., reblogged?).
1. Having empty search fields causes search to fail.
1. The following needs better error handling:
	* "Save curation" without a title (form won't fire presently)
	* "Save curation" without any items (nasty error on the curations list page)
	* Multiple like criteria when searching, i.e., searching for two noticeboards (lowest value used; others disregarded)

## Screenshots ##

1. Screenshot showing a listing of n0tice curations.
2. Screenshot showing curated items, in addition to a new search dialog.

## Changelog ##

### 0.2b ###
* First official beta release.
* Fixed another Javascript bug.

### 0.2a ###
* Fixed a Javascript bug.

### 0.1a ###
* Initial alpha release. OH YES, THERE WILL BE BUGS!

