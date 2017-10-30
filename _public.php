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
# empêcher l'exécution du fichier en dehors de Dotclear
if (!defined('DC_RC_PATH')) { return; }

# Add New Translation English/French
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

# appel css menu
$core->addBehavior('publicHeadContent','tabloidmenu_publicHeadContent');

function tabloidmenu_publicHeadContent($core)
{
	$style = $core->blog->settings->themes->tabloid_menu;
	if (!preg_match('/^menu-cat|simplemenu|menu-no$/',$style)) {
		$style = 'menu-cat';
	}

	$url = $core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme;
	echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$url."/".$style.".css\" />\n";
}

# Exclude Current Post
# Source: http://tips.dotaddict.org/
$core->addBehavior('templateBeforeBlock',array('behaviorsExcludeCurrentPost','templateBeforeBlock'));
class behaviorsExcludeCurrentPost
{
    public static function templateBeforeBlock($core,$b,$attr)
    {
	   if ($b == 'Entries' && isset($attr['exclude_current']) && $attr['exclude_current'] == 1)
	   {
		   return
		   "<?php\n".
		   '$params["sql"] = "AND P.post_url != \'".$_ctx->posts->post_url."\' ";'."\n".
		   "?>\n";
	   }
    }
}


# Add a new class 'category-current' for the parent category
# Source: http://forum.dotclear.net/viewtopic.php?id=37514
$core->addBehavior('templateBeforeBlock',array('tabloidBehavior','block'));
$core->tpl->addValue('CategoryIfCurrent',array('tabloidTpl','CategoryIfCurrent'));

class tabloidBehavior
{
	public static function block()
	{
		$args = func_get_args();
		array_shift($args);

		if ($args[0] == 'Categories') {
			$p =
			'<?php if ($core->url->type != "home") { '.
				'if ($_ctx->exists("categories")) { '.
					'$_ctx->current_cat_id = $_ctx->categories->cat_id; '.
					'$cat_id = $_ctx->categories->cat_id; '.
					'$rs = $core->blog->getCategoryParent($cat_id); '.
					'$_ctx->current_cat_parent_id = $rs->isEmpty() ? 0 : (integer) $rs->cat_id;'. 
				'} elseif ($core->url->type != "home" && $_ctx->exists("posts")) { '.
					'$_ctx->current_cat_id = $_ctx->posts->cat_id; '.
					'$cat_id = $_ctx->posts->cat_id; '.
					'$rs = $core->blog->getCategoryParent($cat_id); '.
					'$_ctx->current_cat_parent_id = $rs->isEmpty() ? 0 : (integer) $rs->cat_id;'.
				'}'.
			"} ?>\n";
			return $p;
		}
	}
}

class tabloidTpl {	
	public static function CategoryIfCurrent($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'category-current';
		$ret = html::escapeHTML($ret);
		$dummy = 'category-not-current';
		$p =
		'<?php if ($_ctx->exists("current_cat_id")) { '.
		'if ($_ctx->categories->cat_id == $_ctx->current_cat_id || $_ctx->categories->cat_id == $_ctx->current_cat_parent_id) { '.
			"echo '".addslashes($ret)."'; } ".
		'else { '.
		"echo '".$dummy."'; } ".
		'} ?>';
		
		return $p;
	}
}


# Check if current post has been updated
# Source: http://forum.dotclear.net/viewtopic.php?id=44438
$core->tpl->addBlock('IfPostUpDate',array('IfPostUpDateTabloid','IfPostUpDate'));
class IfPostUpDateTabloid
{
	public static function IfPostUpDate($attr,$content)
	{
		$delay = isset($attr['delay']) ? $attr['delay'] : '0:1:0';
		$delay = explode(':', $delay);
		switch (count($delay)) {
			case 1:
				$j = $delay[0];
				$h = 0;
				$m = 0;
				break;
			case 2:
				$j = $delay[0];
				$h = $delay[1];
				$m = 0;
				break;
			default:
				$j = $delay[0];
				$h = $delay[1];
				$m = $delay[2];
		}
		$t = ($j * 1440) + ($h * 60) + $m;
		$p = 'if (round((strtotime($_ctx->posts->post_upddt) - strtotime($_ctx->posts->post_creadt)) / 60) > '.$t.'){';
		return '<?php '.$p.' ?>'.
		$content.
		'<?php } ?>';
	}
}


# Add new pagination
# Source: http://tips.dotaddict.org/
$core->tpl->addValue('PaginationLinks', array('tplMyPagination', 'PaginationLinks'));
class tplMyPagination {
	public static function PaginationLinks($attr)
	{
		$p = '<?php
		
		function makePageLink($pageNumber, $linkText) {
			if (isset($GLOBALS["_page_number"])) {
				$current = $GLOBALS["_page_number"];
			} else {
				$current = 1;
			}
			if ($pageNumber != $current) {
				$args = $_SERVER["URL_REQUEST_PART"];
				$args = preg_replace("#(^|/)page/([0-9]+)$#","",$args);
				$url = $GLOBALS["core"]->blog->url.$args;
				if ($pageNumber > 1) {
					$url = preg_replace("#/$#","",$url);
					$url .= "/page/".$pageNumber;
				}
				if (!empty($_GET["q"])) {
					$s = strpos($url,"?") !== false ? "&amp;" : "?";
					$url .= $s."q=".$_GET["q"];
				}
				$linkDesc = $GLOBALS["__l10n"]["Go to page"]."&nbsp;".$linkText;
				return "<span><a href=\"".$url."\" title=\"".$linkDesc."\">".$linkText."</a></span>";
			} else {
				return "<span class=\"this\">".$linkText."</span>";
			}
		}
		
		if (isset($GLOBALS["_page_number"])) {
			$current = $GLOBALS["_page_number"];
		} else {
			$current = 1;
		}
		if ($_ctx->exists("pagination")) {
			$nb_posts = $_ctx->pagination->f(0);
		}
		
		/* Variables to tweak the pagination system */
		$nb_per_page = $_ctx->post_params["limit"][1];
		$nb_pages = ceil($nb_posts/$nb_per_page);
		$nb_sequence = 2 * 3 + 1;
		
		echo "<p>";
		?>';
		
		if (!isset($attr['max'])) { $p .= '<?php $nb_page_max = 0; ?>'; } else { $p .= '<?php $nb_page_max = '.$attr['max'].'; ?>'; }
		$p .= '<?php
		
		if ($nb_page_max == 0 || $nb_pages <= $nb_page_max) {
			for ($i = 1; $i <= $nb_pages; $i++) {
				echo makePageLink($i,$i);
			}
		} else {
			echo makePageLink(1,1);
			$min_page = max($current - ($nb_sequence - 1) / 2, 2);
			$max_page = min($current + ($nb_sequence - 1) / 2, $nb_pages - 1);
			if ($min_page > 2) { echo "<span class=\"etc\">...</span>"; }
			for ($i = $min_page; $i <= $max_page ; $i++) {
				echo makePageLink($i,$i);
			}
			if ($max_page < $nb_pages - 1) { echo "<span class=\"etc\">...</span>"; }
			echo makePageLink($nb_pages,$nb_pages);
		}
		echo "</p>";
		
		?>';
		
		return $p;
	}
}


# Ajax search URL (Modified code from Olivier Meunier http://themes.dotaddict.org/galerie-dc2/details/Noviny)
$core->url->register('ajaxsearch','ajaxsearch','^ajaxsearch(?:(?:/)(.*))?$',array('webSearch','ajaxsearch'));
class webSearch
{
	public static function ajaxsearch($args)
	{
		global $core;
		$res = '';
		$term = $_GET['term'];
		
        try
        {
            if (!$term) {
                throw new Exception;
            }
	
			$q = rawurldecode($term);
			$rs = $core->blog->getPosts(array(
				'search' => $q,
				'limit' => 5,
				'no_content' => 1
			));
			
			if ($rs->isEmpty()) {
				throw new Exception;
			}
			
			$nbEl = $rs->count();
			$el = 0;
			$res = '[';
			while ($rs->fetch())
			{
				//If several results then add comma
				if($el < $nbEl-1) {
					$res .= '"'.html::escapeHTML($rs->post_title).'",';
					$el++;
				}
				//Otherwise don't add comma
				else { $res .= '"'.html::escapeHTML($rs->post_title).'"'; }
			}
			$res .= ']';
		}
        catch (Exception $e) {}		
		
		header('Content-Type: text/plain; charset=UTF-8');
		echo $res;
	}
}