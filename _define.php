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
if (!defined('DC_RC_PATH')) {
    return;
}
$this->registerModule(
    'Tabloid',
    'Fully customizable theme',
    'Azork, Pierre Van Glabeke',
    '2.1-dev',
    [
        'requires' => [['core', '2.24']],
        'type'     => 'theme',
        'tplset'   => 'mustek',
    ]
);
