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
if (!defined('DC_RC_PATH')) { return; }

# Translation
l10n::set(__DIR__ . '/locales/' . dcCore::app()->lang . '/public');

# appel css menu
dcCore::app()->addBehavior('publicHeadContent','tabloidmenu_publicHeadContent');

function tabloidmenu_publicHeadContent()
{
	$style = dcCore::app()->blog->settings->themes->tabloid_menu;
	if (!preg_match('/^menu-cat|simplemenu|menu-no$/', (string) $style)) {
		$style = 'menu-cat';
	}

  $theme_url = dcCore::app()->blog->settings->system->themes_url . '/' . dcCore::app()->blog->settings->system->theme;
  echo '<link rel="stylesheet" type="text/css" media="screen" href="' . $theme_url . '/css/' . $style . ".css\" />\n";
}

# Exclude Current Post
# Source: http://tips.dotaddict.org/
dcCore::app()->addBehavior('templateBeforeBlockV2', ['behaviorsExcludeCurrentPost','templateBeforeBlock']);

class behaviorsExcludeCurrentPost
{
    public static function templateBeforeBlock(string $block, ArrayObject $attr): string
    {
        if ($block == 'Entries' && isset($attr['exclude_current']) && $attr['exclude_current'] == 1) {
            return
            "<?php\n" .
            "if (!isset(\$params)) { \$params = []; }\n" .
            "if (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n" .
            '$params["sql"] .= "AND P.post_url != \'".dcCore::app()->ctx->posts->post_url."\' ";' . "\n" .
            "?>\n";
        }

        return '';
    }
}

# Add a new class 'category-current' for the parent category
# Source: http://forum.dotclear.net/viewtopic.php?id=37514

dcCore::app()->addBehavior('templateBeforeBlockV2', ['tabloidBehavior','templateBeforeBlock']);

class tabloidBehavior
{
    public static function templateBeforeBlock(string $block, ArrayObject $attr): string
    {
        if ($block == 'Categories') {
            $p = '<?php if (dcCore::app()->url->type != "home") { ' .
                'if (dcCore::app()->ctx->exists("categories")) { ' .
                    'dcCore::app()->ctx->current_cat_id = dcCore::app()->ctx->categories->cat_id; ' .
                    '$cat_id = dcCore::app()->ctx->categories->cat_id; ' .
                    '$rs = dcCore::app()->blog->getCategoryParents($cat_id); ' .
                    'dcCore::app()->ctx->current_cat_parent_id = $rs->isEmpty() ? 0 : (integer) $rs->cat_id;' .

                '} elseif (dcCore::app()->ctx->exists("posts") && dcCore::app()->url->type == "post") { ' .
                    'dcCore::app()->ctx->current_cat_id = dcCore::app()->ctx->posts->cat_id; ' .
                    '$cat_id = dcCore::app()->ctx->posts->cat_id; ' .
                    '$rs = dcCore::app()->blog->getCategoryParents($cat_id); ' .
                    'dcCore::app()->ctx->current_cat_parent_id = $rs->isEmpty() ? 0 : (integer) $rs->cat_id;' .

                '}' .
            "} ?>\n";

            return $p;
        }

        return '';
    }
}

dcCore::app()->tpl->addValue('CategoryIfCurrent', ['tabloidTpl','CategoryIfCurrent']);

class tabloidTpl
{
    public static function CategoryIfCurrent($attr)
    {
        $ret = $attr['return'] ?? 'category-current';
        $ret = html::escapeHTML($ret);
        $p   = '<?php if (dcCore::app()->ctx->exists("current_cat_id")) { ' .
        'if (dcCore::app()->ctx->categories->cat_id == dcCore::app()->ctx->current_cat_id || dcCore::app()->ctx->categories->cat_id == dcCore::app()->ctx->current_cat_parent_id) { ' .
            "echo ' class=\"" . addslashes($ret) . "\"'; } " .
        '} ?>';

        return $p;
    }
}

# Check if current post has been updated
# Source: http://forum.dotclear.net/viewtopic.php?id=44438
dcCore::app()->tpl->addBlock('IfPostUpDate', ['IfPostUpDateTabloid','IfPostUpDate']);
class IfPostUpDateTabloid
{
    public static function IfPostUpDate($attr, $content)
    {
        $delay = $attr['delay'] ?? '0:1:0';
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
        $p = 'if (round((strtotime(dcCore::app()->ctx->posts->post_upddt) - strtotime(dcCore::app()->ctx->posts->post_creadt)) / 60) > ' . $t . '){';

        return '<?php ' . $p . ' ?>' .
        $content .
        '<?php } ?>';
    }
}

# Add new pagination
# Source: http://tips.dotaddict.org/
dcCore::app()->tpl->addValue('PaginationLinks', ['tplMyPagination', 'PaginationLinks']);
class tplMyPagination
{
    public static function PaginationLinks($attr)
    {
        $p = '<?php
		
		function makePageLink($pageNumber, $linkText) {
			if (dcCore::app()->public->getPageNumber() !== null) {
				$current = dcCore::app()->public->getPageNumber();
			} else {
				$current = 1;
			}
			if ($pageNumber != $current) {
				$args = $_SERVER["URL_REQUEST_PART"];
				$args = preg_replace("#(^|/)page/([0-9]+)$#","",$args);
				$url = dcCore::app()->blog->url.$args;
				if ($pageNumber > 1) {
					$url = preg_replace("#/$#","",$url);
					$url .= "/page/".$pageNumber;
				}
				if (!empty($_GET["q"])) {
					$s = strpos($url,"?") !== false ? "&amp;" : "?";
					$url .= $s."q=".$_GET["q"];
				}
				$linkDesc = "Page &nbsp;".$linkText;
				return "<span><a href=\"".$url."\" title=\"".$linkDesc."\">".$linkText."</a></span>";
			} else {
				return "<span class=\"this\">".$linkText."</span>";
			}
		}
		
		if (dcCore::app()->public->getPageNumber() !== null) {
			$current = dcCore::app()->public->getPageNumber();
		} else {
			$current = 1;
		}
		if (dcCore::app()->ctx->exists("pagination")) {
			$nb_posts = dcCore::app()->ctx->pagination->f(0);
		}
		
		/* Variables to tweak the pagination system */
		$nb_per_page = dcCore::app()->ctx->post_params["limit"][1];
		$nb_pages = ceil($nb_posts/$nb_per_page);
		$nb_sequence = 2 * 3 + 1;
		
		echo "<p>";
		?>';

        if (!isset($attr['max'])) {
            $p .= '<?php $nb_page_max = 0; ?>';
        } else {
            $p .= '<?php $nb_page_max = ' . $attr['max'] . '; ?>';
        }
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
dcCore::app()->url->register('ajaxsearch','ajaxsearch','^ajaxsearch(?:(?:/)(.*))?$',array('webSearch','ajaxsearch'));
class webSearch
{
	public static function ajaxsearch($args)
	{
		$res = '';
		$term = $_GET['term'];
		
        try
        {
            if (!$term) {
                throw new Exception;
            }
	
			$q = rawurldecode($term);
			$rs = dcCore::app()->blog->getPosts(array(
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
