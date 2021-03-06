<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\build_script;

use pocketmine\utils\VersionString;
use function dirname;
use function fgets;
use function file_get_contents;
use function file_put_contents;
use function preg_quote;
use function preg_replace;
use function sleep;
use function sprintf;
use function system;
use const pocketmine\BASE_VERSION;
use const STDIN;

require_once dirname(__DIR__) . '/src/pocketmine/VersionInfo.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

$currentVer = new VersionString(BASE_VERSION);
$nextVer = new VersionString(sprintf(
	"%u.%u.%u",
	$currentVer->getMajor(),
	$currentVer->getMinor(),
	$currentVer->getPatch() + 1
));


$versionInfoPath = dirname(__DIR__) . '/src/pocketmine/VersionInfo.php';
$versionInfo = file_get_contents($versionInfoPath);

file_put_contents($versionInfoPath, preg_replace(
	'/^const IS_DEVELOPMENT_BUILD = true;$/m',
	'const IS_DEVELOPMENT_BUILD = false;',
	$versionInfo
));
echo "please add appropriate notes to the changelog and press enter...";
fgets(STDIN);
system('git add "' . dirname(__DIR__) . '/changelogs"');
system('git commit -m "Release ' . BASE_VERSION . '" --include "' . $versionInfoPath . '"');
system('git tag ' . BASE_VERSION);
file_put_contents($versionInfoPath, $mod =  preg_replace(
	$pattern = '/^const BASE_VERSION = "' . preg_quote(BASE_VERSION, '/') . '";$/m',
	'const BASE_VERSION = "' . $nextVer->getBaseVersion() . '";',
	$versionInfo
));
system('git add "' . $versionInfoPath . '"');
system('git commit -m "' . $nextVer->getBaseVersion() . ' is next" --include "' . $versionInfoPath . '"');
echo "pushing changes in 10 seconds\n";
sleep(10);
system('git push origin HEAD ' . BASE_VERSION);
