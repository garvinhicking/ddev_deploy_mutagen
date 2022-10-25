<?php
// The filename should be adjusted to match the TYPO3_CONTEXT environment variable
// also used for e.g. the AdditionalConfiguration file.

// Deployment is performed in DDEV via:
// ddev exec "cd deployment && vendor/bin/surf deploy Production.Example"

$configuration = [
    // This is just a name which describes the deployed application
    // As the node is detected automatically by using the name of this configuration file
    // This name is mostly used for verbosed output and log files
    'applicationName' => 'example.com',
    // The remote GIT repository to clone as a base for the deployment
    // Surf need read access for this repository e.g. by setting a deploy key in the GitHub repository settings
    'repositoryUrl' => 'git@github.com:faktore-git/my-project.git',
    'gitBranch' => 'relaunch',
    // The target machine and SSH user name to deploy the application to. Surf needs SSH access using the ~/.ssh/id_rsa key
    'hostName' => 'faktore@staging.example.com',
    // Path and filename to the PHP binary. Make sure to set the correct version if multiple PHP versions are available on the machine
    'phpBinary' => '/usr/local/bin/php',
    // The deployment path contains a folder 'releases' where every deployment cycle generates a new, unique release directory
    'deploymentPath' => '/var/www/www.example.com/',
    // The path to the binary of the TYPO3 console relative to the deployment path
    'baseUrl' => 'https://staging.example.com/',
    'typo3ConsoleBinary' => 'vendor/bin/typo3cms',
    // The webDirectory represents your document root relative to the deployment path, not the application root
    // Check on which folder the virtual host configuration points to
    'webDirectory' => 'htdocs/web',
    // A list of files and folder to exclude during transfer to the target machine
    'rsyncExcludes' => [
        '/.git',
        '/.gitignore',
        'readme.md',
        '/vagrant',
        '/deployment',
        '/shared',
        '/.ddev',
        '/migration',
        '/htdocs/web/uploads',
        '/frontend'
    ],
    // The number of releases to keep when a new deployment is performed
    // Older releases will be deleted during the cleanup process of the deployment cycle
    'keepReleases' => 3,
    // The NPM install command. Usually you don't have to change anything here
    'npmInstallCommand' => 'cd {workspacePath}/frontend && npm install 2>&1',
    // The frontend build chain command. Usually only have to adjust the path to the build tool and the build command
    'frontendBuildCommand' => '{workspacePath}/frontend/node_modules/gulp-cli/bin/gulp.js cms --cwd {workspacePath}/frontend --force 2>&1'
];

$sshParts = explode('@', $configuration['hostName']);
$sshUser = $sshParts[0];
$hostName = $sshParts[1];

/** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */
$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();

$environment = basename(__FILE__, '.php');
$environmentParts = explode('.', $environment);
$mainContext = $environmentParts[0];
$subContext = $environmentParts[1];

$liveNode = new \TYPO3\Surf\Domain\Model\Node($environment);
$liveNode->setHostname($hostName)
    ->setOption('username', $sshUser)
    ->setOption('privateKeyFile', '/var/www/html/deployment/ssh/id_rsa');

$application = new \TYPO3\Surf\Application\TYPO3\CMS($configuration['applicationName']);
$application->addNode($liveNode)
    ->setOption('useApplicationWorkspace', false)
    ->setOption('phpBinaryPathAndFilename', $configuration['phpBinary'])
    ->setOption('composerCommandPath', 'composer')
    ->setOption('keepReleases', $configuration['keepReleases'])
    ->setOption('repositoryUrl', $configuration['repositoryUrl'])
    ->setOption('branch', $configuration['gitBranch'])

    ->setOption('scriptFileName', $configuration['typo3ConsoleBinary'])
    ->setOption('webDirectory', $configuration['webDirectory'])
    ->setOption('baseUrl', $configuration['baseUrl'])
    ->setOption(TYPO3\Surf\Task\TYPO3\CMS\FlushCachesTask::class . '[arguments]', [])

    ->setOption('composerJsonPath', 'htdocs')
    ->setOption('context', $mainContext . '/' . $subContext)
    ->setOption('rsyncExcludes', $configuration['rsyncExcludes'])
    ->setOption('scriptIdentifier', time())

    ->setOption(
        'scriptBasePath',
        \TYPO3\Flow\Utility\Files::concatenatePaths([$deployment->getWorkspacePath($application), 'htdocs/web'])
    )
    ->setDeploymentPath($configuration['deploymentPath']);

$deployment->addApplication($application);

$workflow->defineTask(
    'Faktore\\Surf\\DefinedTask\\Node\\LocalInstallTask',
    'TYPO3\\Surf\\Task\\LocalShellTask', ['command' => $configuration['npmInstallCommand']]
);
$workflow->defineTask(
    'Faktore\\Surf\\DefinedTask\\Gulp\\LocalBuildTask',
    'TYPO3\\Surf\\Task\\LocalShellTask', ['command' => $configuration['frontendBuildCommand']]
);

/* UGC task -> decide if shared/Data/fileadmin or only shared/fileadmin/ is used! */
$workflow->defineTask(
    'Faktore\\Surf\\DefinedTask\\Node\\EnvironmentSymLinkTask',
    'TYPO3\\Surf\\Task\\ShellTask',
    [
        'command' =>
            [
                "ln -sf " . $configuration['deploymentPath'] . "shared/.env " . $deployment->getApplicationReleasePath($application) . "/htdocs/.env",
                "rm -rf " . $deployment->getApplicationReleasePath($application) . "/" . $configuration['webDirectory'] . "/fileadmin/",
                "ln -sf " . $configuration['deploymentPath'] . "shared/fileadmin " . $deployment->getApplicationReleasePath($application) . "/" . $configuration['webDirectory'] . "/fileadmin",
                "ln -sf " . $configuration['deploymentPath'] . "shared/uploads " . $deployment->getApplicationReleasePath($application) . "/" . $configuration['webDirectory'] . "/uploads",
                "ln -sf " . $configuration['deploymentPath'] . "shared/typo3conf/l10n " . $deployment->getApplicationReleasePath($application) . "/" . $configuration['webDirectory'] . "/typo3conf/l10n",
                "ln -sf " . $configuration['deploymentPath'] . "shared/typo3conf/AdditionalConfiguration.Credentials.php " . $deployment->getApplicationReleasePath($application) . "/" . $configuration['webDirectory'] . "/typo3conf/AdditionalConfiguration.Credentials.php",
                "mkdir " . $deployment->getApplicationReleasePath($application) . "/" . $configuration['webDirectory'] . "/typo3temp",
            ]

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
                "// sleep 600",
            ]

    ]
);

/** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */
$deployment->setWorkflow($workflow);

$deployment->onInitialize(function() use ($workflow, $application) {
    $workflow->afterTask(
        \TYPO3\Surf\Task\Generic\CreateSymlinksTask::class,
        'Faktore\\Surf\\DefinedTask\\Node\\EnvironmentSymLinkTask',
        $application
    );

    // SymLinks are created with a custom task
    $workflow->removeTask('TYPO3\\Surf\\Task\\TYPO3\\CMS\\SymlinkDataTask');
    // PackageStates is delivered via GIT
    $workflow->removeTask('TYPO3\\Surf\\Task\\TYPO3\\CMS\\CreatePackageStatesTask');

    /* Debugging example
    $workflow->beforeTask(
        'TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask',
        'Faktore\\Surf\\DefinedTask\\DebugTask',
        $application
    );
    */

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
});

/** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */
$deployment->setWorkflow($workflow);

if (method_exists($deployment, 'setRelativeProjectRootPath')) {
    $deployment->setRelativeProjectRootPath('htdocs');
}
