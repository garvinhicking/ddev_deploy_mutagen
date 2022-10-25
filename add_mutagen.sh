#!/bin/bash

if [ ! -f ".ddev/config.yaml" ] ; then
	echo "FATAL ERROR: Run the script within a directory containing .ddev/!"
	exit
fi

if [ -f ".ddev/mutagen/mutagen.yml" ] ; then
	echo "FATAL ERROR: .ddev/mutagen/mutagen.yml already exists! You may want to delete it to re-create (make a backup and diff?)"
	exit
fi

if [ -f ".ddev/config.mutagen.yml" ] ; then
	echo "FATAL ERROR: .ddev/config.mutagen.yml already exists! You may want to delete it to re-create (make a backup and diff?)"
	exit
fi

if grep -q "mutagen_enabled: true" ".ddev/config.yaml"; then
	echo "FATAL ERROR: Mutagen is already enabled!"
	exit
fi

if ddev config global | grep -q "mutagen-enabled=true"; then
	echo "FATAL ERROR: Mutagen is enabled globally!"
	exit
fi

echo "[+] Enabling mutagen for this project"

echo "mutagen_enabled: true" > ./.ddev/config.mutagen.yml

if [ ! -d ".ddev/mutagen" ] ; then
	mkdir .ddev/mutagen
fi

export MUTAGEN_TEMPLATE=$(cat << 'EOF'
sync:
  defaults:
    mode: "two-way-resolved"
    stageMode: "neighboring"
    ignore:
      paths:
      # The top-level .git directory is ignored because where possible it's mounted
      # into the container with a traditional docker bind-mount
      - "/.git"

      - "/.tarballs"
      - "/.ddev/db_snapshots"
      - "/.ddev/.importdb*"
      - ".DS_Store"
      - ".idea"

      - "/htdocs/web/fileadmin"


      # You can also exclude other directories from mutagen-syncing
      # For example /var/www/html/var does not need to sync in TYPO3
      # so you can add:
      # - "/var"
      # vcs like .git can be ignored for safety, but then some
      # composer operations may fail if they use dev versions/git.
      # vcs: true
    symlink:
      mode: "posix-raw"

    # This is the actual reason for a custom mutagen.yml file:
    permissions:
      defaultFileMode: 0644
      defaultDirectoryMode: 0755
EOF
);

echo "$MUTAGEN_TEMPLATE" > .ddev/mutagen/mutagen.yml

echo "[+] Wrote .ddev/mutagen/mutagen.yml"

