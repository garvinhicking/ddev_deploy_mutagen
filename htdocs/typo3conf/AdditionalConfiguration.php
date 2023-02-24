<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$TYPO3_CONTEXT = (string)\TYPO3\CMS\Core\Core\Environment::getContext();

$filename = str_replace('/', '.', $TYPO3_CONTEXT);

if (file_exists(realpath(dirname(__FILE__)) . '/AdditionalConfiguration.Credentials.php')) {
    include realpath(dirname(__FILE__)) . '/AdditionalConfiguration.Credentials.php';
}

if (file_exists(realpath(dirname(__FILE__)) . '/AdditionalConfiguration.Local.' . $filename . '.php')) {
    include realpath(dirname(__FILE__)) . '/AdditionalConfiguration.Local.' . $filename . '.php';
} elseif (file_exists(realpath(dirname(__FILE__)) . '/AdditionalConfiguration.Server.' . $filename . '.php')) {
    include realpath(dirname(__FILE__)) . '/AdditionalConfiguration.Server.' . $filename . '.php';
}

