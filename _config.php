<?php
# BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Tabloid, a Dotclear 2 theme.
#
# Copyright (c) 2010-2017 Azork
# Contributor: Pierre Van Glabeke - https://github.com/brol/tabloid
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ---------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

//PARAMS

# Translations
l10n::set(__DIR__ . '/locales/' . dcCore::app()->lang . '/main');

# Default values
$default_menu = 'menu-cat';

# Settings
$my_menu = dcCore::app()->blog->settings->themes->tabloid_menu;

# Menu type
$tabloid_menu_combo = array(
	__('Categories first level') => 'menu-cat',
	__('simpleMenu') => 'simplemenu',
	__('none') => 'menu-no'
);

// POST ACTIONS

if (!empty($_POST))
{
	try
	{
		dcCore::app()->blog->settings->addNamespace('themes');

		# Menu type
		if (!empty($_POST['tabloid_menu']) && in_array($_POST['tabloid_menu'],$tabloid_menu_combo))
		{
			$my_menu = $_POST['tabloid_menu'];

		} elseif (empty($_POST['tabloid_menu']))
		{
			$my_menu = $default_menu;

		}
		dcCore::app()->blog->settings->themes->put('tabloid_menu',$my_menu,'string','Menu to display',true);

		// Blog refresh
		dcCore::app()->blog->triggerBlog();

		// Template cache reset
		dcCore::app()->emptyTemplatesCache();

		dcPage::success(__('Theme configuration has been successfully updated.'),true,true);
	}
	catch (Exception $e)
	{
		dcCore::app()->error->add($e->getMessage());
	}
}

// DISPLAY

# Menu
echo
'<div class="fieldset"><h4>'.__('Menu').'</h4>'.
'<p class="field"><label>'.__('Menu to display:').'</label>'.
form::combo('tabloid_menu',$tabloid_menu_combo,$my_menu).
'</p>'.
'</div>';