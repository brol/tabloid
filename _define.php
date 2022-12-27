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
if (!defined('DC_RC_PATH')) {
    return;
}
$this->registerModule(
    'Tabloid',
    'Fully customizable theme',
    'Azork, Pierre Van Glabeke',
    '2.1',
    [
        'requires' => [['core', '2.24']],
        'type'     => 'theme',
        'tplset'   => 'mustek',
    ]
);
