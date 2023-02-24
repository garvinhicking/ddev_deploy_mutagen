<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] = 'TYPO3-Surf (Development DDEV)';

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'noreply@example.local';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'TYPO3-Surf ddev';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'smtp';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_server'] = 'localhost:1025';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = ".*";

// vagrant
$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword'] = '$argon2i$v=19$m=65536,t=16,p=1$MWN3SmVHeUtHNndNV1hyLw$XZXCish1u9//XQ56Ml4cLQInMcc1xWdzK1+LGLYVqV8';

$GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path'] = '/usr/bin/';
$GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path_lzw'] = '/usr/bin/';
$GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'] = 'GraphicsMagick';
