<?php
/**
 * @brief Tabloid, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Theme
 *
 * @author Azork (http://xtradotfreedotfr.free.fr/blog/), Pierre Van Glabeke
 *
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

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