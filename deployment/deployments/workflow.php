<?php
// This file is never meant to be edited.

// Always only edit the files like `Production.Production.php` / `Production.Staging.php` etc. pp
// Whenever you feel the need to change something contained in this file, instead create a configuration
// option for it and make it abstracted.


if (!isset($configuration) || !is_array($configuration)) {
    die('This script is not meant to be called on its own.');
}

// $deployment comes from the overlying architecture!
/** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

// Check CONTEXT
if (isset($configuration['typo3Context']) && !empty($configuration['typo3Context'])) {
    $environmentParts = explode('/', $configuration['typo3Context']);
    $environment      = $environmentParts[0] . '.' . $environmentParts[1];
} else {
    $environment      = basename($configuration['_self'], '.php');
    $environmentParts = explode('.', $environment);
}

$mainContext = $environmentParts[0];
$subContext  = $environmentParts[1];

// Get SSH deployment target
// Allows two notations, either "user@host" or just "host".
$sshParts = explode('@', $configuration['hostName']);

if (isset($sshParts[1])) {
    $sshUser  = $sshParts[0];
    $hostName = $sshParts[1];
} else {
    $sshUser  = `whoami`;
    $hostName = $sshParts[0];
}

// Setup deployment target
$liveNode = new \TYPO3\Surf\Domain\Model\Node($environment);
$liveNode->setHostname($hostName)
    ->setOption('username', $sshUser)
    ->setOption('privateKeyFile', $configuration['_sshKey']);

// Propagate configuration values to SURF Options
$application = new \TYPO3\Surf\Application\TYPO3\CMS();
$application->addNode($liveNode)
    ->setOption('useApplicationWorkspace',      $configuration['_useApplicationWorkspace'])
    ->setOption('phpBinaryPathAndFilename',     $configuration['phpBinary'])
    ->setOption('composerCommandPath',          $configuration['_composerCommandPath'])
    ->setOption('keepReleases',                 $configuration['keepReleases'])
    ->setOption('repositoryUrl',                $configuration['repositoryUrl'])
    ->setOption('branch',                       $configuration['gitBranch'])

    ->setOption('scriptFileName',               $configuration['typo3ConsoleBinary'])
    ->setOption('typo3CliBinary',               $configuration['_typo3CliBinary'])
    ->setOption('webDirectory',                 $configuration['webDirectory'])
    ->setOption('baseUrl',                      $configuration['baseUrl'])

    ->setOption('context',                      $mainContext . '/' . $subContext)
    ->setOption('rsyncExcludes',                $configuration['rsyncExcludes'])
    ->setOption('scriptIdentifier',             time())

    ->setOption(
        TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask::class . '[arguments]',
        []
    )

    ->setOption(
        'scriptBasePath',
        \TYPO3\Flow\Utility\Files::concatenatePaths([$deployment->getWorkspacePath($application), $configuration['webDirectory']])
    )
    ->setDeploymentPath($configuration['deploymentPath']);

$deployment->addApplication($application);

// Stitch Symlinks
$symlinks = [];
if (isset($configuration['symlinks']) && is_array($configuration['symlinks'])) {
    foreach($configuration['symlinks'] AS $symlinkFrom => $symlinkTo) {
        $symlinks[] = "ln -sf " . $configuration['deploymentPath'] . $symlinkFrom . " " . $deployment->getApplicationReleasePath($liveNode) . $symlinkTo;
    }
}

if (isset($configuration['keepTYPO3Caches'])) {
    $varParts = ['log', 'session', 'lock', 'charset', 'transient'];

    foreach($varParts AS $varPart) {
        // Remove directory that may have been created due to TYPO3 console on deployment stage. The directory would be empty. Should be no harm done. Blame Lars if it did. ;)
        $symlinks[] = "rm -rf " . $deployment->getApplicationReleasePath($liveNode) . "/" . $configuration['webDirectory'] . "/var/" . $varPart;

        // If this is a first deployment, the shared/Data/var/ directory with its subdirectories might not yet exist, so create it in that case
        $symlinks[] = "mkdir -p " . $deployment->getApplicationReleasePath($liveNode) . "/" . $configuration['sharedDirectory'] . "/var/" . $varPart;

        // Now attach the symlink from shared to DocRoot.
        $symlinks[] = "ln -sf " . $configuration['deploymentPath'] . $configuration['sharedDirectory'] . "/var/" . $varPart . " " . $deployment->getApplicationReleasePath($liveNode) . "/" . $configuration['webDirectory'] . "/var/" . $varPart;
    }
}

$deployment->onInitialize(function() use ($deployment, $application, $configuration, $symlinks) {
    $workflow = $deployment->getWorkflow();

    $workflow->defineTask(
        'Faktore\\Surf\\DefinedTask\\Node\\LocalInstallTask',
        'TYPO3\\Surf\\Task\\LocalShellTask', ['command' => $configuration['npmInstallCommand']]
    );
    $workflow->defineTask(
        'Faktore\\Surf\\DefinedTask\\Gulp\\LocalBuildTask',
        'TYPO3\\Surf\\Task\\LocalShellTask', ['command' => $configuration['frontendBuildCommand']]
    );

    $workflow->defineTask(
        'Faktore\\Surf\\DefinedTask\\Node\\EnvironmentSymLinkTask',
        'TYPO3\\Surf\\Task\\ShellTask',
        [
            'command' => $symlinks
        ]
    );

    // This is a possible debugging task to allow executing remote commands before a
    // rollback is performed. Move it to after/beforeStage where needed.
    $workflow->defineTask(
        'Faktore\\Surf\\DefinedTask\\DebugTask',
        'TYPO3\\Surf\\Task\\ShellTask',
        [
            'command' =>
                [
                    "sleep " . $configuration['_sleepDebugTimeout'],
                ]

        ]
    );

    // Inject custom SymlinkTask
    $workflow->afterTask(
        \TYPO3\Surf\Task\Generic\CreateSymlinksTask::class,
        'Faktore\\Surf\\DefinedTask\\Node\\EnvironmentSymLinkTask',
        $application
    );

    // SymLinks are created with the custom task above
    $workflow->removeTask('TYPO3\\Surf\\Task\\TYPO3\\CMS\\SymlinkDataTask');

    if ($configuration['_sleepDebug']) {
        $workflow->beforeTask(
            '_sleepDebugTask',
            'Faktore\\Surf\\DefinedTask\\DebugTask',
            $application
        );
    }

    // Inject NODE build tasks
    $workflow->afterTask(
        'TYPO3\\Surf\\DefinedTask\\Composer\\LocalInstallTask',
        'Faktore\\Surf\\DefinedTask\\Node\\LocalInstallTask',
        $application
    );
    $workflow->afterTask(
        'Faktore\\Surf\\DefinedTask\\Node\\LocalInstallTask',
        'Faktore\\Surf\\DefinedTask\\Gulp\\LocalBuildTask',
        $application
    );
    $workflow->beforeTask(
        'TYPO3\\Surf\\Task\\SymlinkReleaseTask',
        'Faktore\\Surf\\Task\\TYPO3\\CMS\\UpdateTranslationsTask',
        $application
    );

    if (isset($configuration['resetWebCache']) && $configuration['resetWebCache']) {
        // TODO: APC reset script
        $workflow->beforeStage('transfer', \TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask::class, $application)
            ->afterStage('switch', \TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask::class, $application);

        // TODO: Respect HTTPAuthDeployment credentials for calling.
    }
});

