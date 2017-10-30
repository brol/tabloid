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
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"Tabloid",
	/* Description*/	"Fully customizable theme",
	/* Author */		"Azork, Pierre Van Glabeke",
	/* Version */		'2.0',
	array(
		'type'	 =>	'theme',
		'tplset' => 'mustek',
		'dc_min' => '2.12'
	)
);