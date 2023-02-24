# What's this?

A dummy example of deployment configuration. The project is a naked TYPO3
v11 installation through composer, and is able to deploy to its own
instance.

Be sure to import the SQL dump `build/init.sql`.

These URLs are viable:

https://typo3-surf.ddev.site (Workspace/Development)
https://deployment.typo3-surf.ddev.site (Deployment)

The "workspace" TYPO3 lives in the default installation. The

*IMPORTANT*: This repository versions several key files you would never want
to check into a proper project (everything in `deployment_target/`,
especially credentials). 

DO NOT USE THIS PROJECT AS A TEMPLATE FOR ANYTHING.

# How to test

* Check out repository
* `ddev start`
* `ddev exec composer install`
* `ddev exec "cd deployment && composer install"`
* `ddev import-db build/init.sql`
* `ddev nvm install`

Credantials are: `admin` : `vagrant123`. Install-Tool Password is `vagrant`.

# Caveats

## SSH

This repository makes ddev install `openssh-server` and starts it via a hook/script:

`/etc/init.d/ssh start`
`cat /var/www/html/deployment/ssh/id_rsa.pub >> ~/.ssh/authorized_keys`

This is needed to allow the DDEV container to SSH into itself.

## htdocs

`htdocs/web/` had to be changed to only `htdocs/` in favor of being able to
put composer.json into the root directory.

TYPO3 Surf doesn't behave well when it needs to be configured for a
different directory.

This means, our commonly used documentRoots need to be changed from
`releases/current/htdocs/web` to just `releases/current/htdocs/`.

Also the ddev config gets a new docRoot configuration of just `docroot: htdocs`.

The `composer.json` now also only defines `"extra"."typo3/cms"."web-dir":"htdocs"`.

### Advantages of a root composer.json:
* Better phpstorm integration
* TYPO3 Surf standard compliant

### Disadvantages of a root composer.json:
* It's no longer as isolated on what is used for the web-side of things, and what other stuff a repository delivers. `vendor` is now in the root instead of `htdocs/vendor`.
* You might assume that everything in the root is related to the project's hosting, while in fact other build-files or NPM/frontend files are delivered that are parallel to the scope of `htdocs` earlier.
