<?php
// This file contains configuration for a deployment.

// Deployment is executed via:
// vendor/bin/surf deploy <FILENAME>
// FILENAME corresponds to the name of this file MINUS the .php ending, so:
//
// vendor/bin/surf deploy Production.Self
//
// Outside ddev you can execute:
// ddev exec "cd deployment && vendor/bin/surf deploy Production.Self"

$configuration = [
    // This is just a name which describes the deployed application (used for verbose output, logfiles - no special characters please)
    'applicationName'           => 'typo3-surf',
    'baseUrl'                   => 'https://deployment.typo3-surf.ddev.site',

    // The remote GIT repository to clone as a base for the deployment
    // Surf need read access for this repository e.g. by setting a deploy key in the GitHub repository settings
    'repositoryUrl'             => 'https://github.com/garvinhicking/ddev_deploy_mutagen.git', // usually: 'git@github.com/faktore-git/project.git
    'gitBranch'                 => 'surf', // usually: production, staging, development, feature/XXX

    // If empty, will be deduced from filename (i.e. "Production.Production.php")
    'typo3Context'              => 'Production/Production',

    // The target machine and SSH user name to deploy the application to. Surf needs SSH access using the ~/.ssh/id_rsa key
    // Usually "p12345@p12345.mittwaldserver.info
    'hostName'                  => 'localhost',

    // The deployment path contains a folder 'releases' where every deployment cycle generates a new, unique release directory
    'deploymentPath'            => '/var/www/html/deployment_target/',

    // The webDirectory represents your document root relative to the deployment path, not the application root (check VirtualHost!)
    'webDirectory'              => 'htdocs/',
    'sharedDirectory'           => 'shared/Data/',

    // Path and filename to the PHP binary. Make sure to set the correct version if multiple PHP versions are available on the machine
    'phpBinary'                 => '/usr/bin/php',

    // The path to the binary of the TYPO3 console relative to the deployment path
    'typo3ConsoleBinary'        => 'vendor/bin/typo3cms',

    // UGC symlinks, using available placeholders:
    // {sharedDirectory}, {webDirectory}
    'symlinks'                  => [
        "{sharedDirectory}/.env"
        => "/.env",

        "{sharedDirectory}/fileadmin"
        =>"/{webDirectory}/fileadmin",

        "{sharedDirectory}/uploads"
        => "/{webDirectory}/uploads",

        "{sharedDirectory}//AdditionalConfiguration.Credentials.php"
        => "/{webDirectory}/typo3conf/AdditionalConfiguration.Credentials.php",
    ],

    // A list of files and folder to exclude during transfer to the target machine
    'rsyncExcludes'             => [
        '/.ddev',
        '/.git',
        '/.gitignore',
        '/vagrant',
        '/deployment',
        '/deployment_target'
        '/shared',
        '/migration',
        '/build',
        '/htdocs/uploads',
        '/frontend',
        'readme.md'
    ],

    // The number of releases to keep when a new deployment is performed
    // Older releases will be deleted during the cleanup process of the deployment cycle
    'keepReleases'              => 3,

    // Whether to call a PHP script via URL that is able to reset a OpCode Cache (APC)
    'resetWebCache'             => true,
    // For staging environments, the call may use HTTP authentication, that can be entered here.
    'HTTPAuthDeployment'        => 'user:pass',

    // If enabled, TYPO3 caches in the var/ directory will be symlinked instead of contained within a deployment
    'keepTYPO3Caches'           => true,

    // The NPM install command. Usually you don't have to change anything here
    'npmInstallCommand'         => 'cd {workspacePath}/frontend && npm install 2>&1',
    // The frontend build chain command. Usually only have to adjust the path to the build tool and the build command
    'frontendBuildCommand'      => 'cd {workspacePath}/frontend && npm run build --force 2>&1',
    // If there is no frontend build chain, just use 'true' here

    // ===================================================================
    // From here on those are variables that usually do not need changing.
    // ===================================================================

    // A pointer to the current filename, to be used in workflow
    '_self'                     => __FILE__,

    // SSH Key used for remote identification
    '_sshKey'                   => '/var/www/html/deployment/ssh/id_rsa',

    // Local composer command
    '_composerCommandPath'      => 'composer',

    // Use local workspace instead of a distinct one
    '_useApplicationWorkspace'  => false,

    // TYPO3 CLI Binary location
    '_typo3CliBinary'           => 'vendor/bin/typo3',

    // If deployment fails, it's helpful to place a "breakpoint" to pause deployment,
    // so that you can inspect what's happening on the remote end of things.
    // By default, the task can sleep for 10 Minutes and will then continue (and possibly fail)
    // If you enable this, and perform things on the remote, remember to either kill the sleep task or wait
    // for it to finish. The most helpful debug Task is "TYPO3\Surf\Task\TYPO3\CMS\SetUpExtensionsTask" because
    // that happens first when the remote installation is getting accessed. Usual errors include missing
    // Credentials.php file, missing symlinks, wrong documentroot, missing database, sleep deprevation
    '_sleepDebug'               => false,
    '_sleepDebugTimeout'        => 600,
    '_sleepDebugTask'           => 'TYPO3\\Surf\\Task\\TYPO3\\CMS\\SetUpExtensionsTask'
];

require __DIR__ . '/workflow.php';
