echo "Starting deploy..";
rsync --exclude-from=".rignore" --progress -avz --no-perms . alashov@berke.li:web/imgf
