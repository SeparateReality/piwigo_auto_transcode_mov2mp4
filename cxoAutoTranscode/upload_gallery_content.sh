#!/usr/bin/env bash
#
# Bash script to manually import many files from ./galleries
#
# Usage:
# * put this file in ./piwigo/galleries
# * add your data in config below
# * call script to "upload" gallery folders
# * if all went well -> remove *.bak files
#

# -e  : exit on error
# -u  : error on undefined var
# -o pipefail : capture pipeline errors
# -x  : echo every command (debug)
set -euo pipefail
#set -x

# --------------------------------------------------------------------------- #
# CONFIG                                                                      #
# --------------------------------------------------------------------------- #
base="http://localhost/ws.php"    # Piwigo API endpoint
user="admin"                      # username
pass="your-admin-pw-here"         # password
album=39                          # target album ID
dir="./Archiv"                    # source folder
# --------------------------------------------------------------------------- #

# 1) Login – store session cookie
curl -s -c cookies.txt \
     -d "method=pwg.session.login&username=${user}&password=${pass}" \
     "${base}" > /dev/null

# 2) Upload loop
shopt -s nullglob
readarray -t files < <(printf '%s\n' "$dir"/*.* | sort)
total=${#files[@]}
echo "total files: $total"

i=0

for file in "${files[@]}"; do
  [[ -f $file ]] || continue                # skip folder
  echo "  loop file: $file"
  ((++i))
  fname=$(basename "$file")
  name_noext="${fname%.*}"         # title (filename without extension)

  # --- Build tags -------------------------------------------------------- #
  # Split at "-" and use every segment (trim spaces) as a tag
  IFS='-' read -ra parts <<< "$name_noext"
  tags=""
  for part in "${parts[@]}"; do
    part="${part// /-}"            # replace spaces with dashes
    [[ -z $part ]] && continue
    tags+="${part}, "
  done
  tags="${tags%, }"                # strip trailing comma+space
  echo "  tags: $tags"

  # --- Upload ------------------------------------------------------------ #
  curl -s -b cookies.txt -F method=pwg.images.addSimple \
       -F image=@"$file" \
       -F category="$album" \
       -F name="$name_noext" \
       -F tags="$tags" \
       "$base" > /dev/null
  # ----------------------------------------------------------------------- #

  # Rename file after successful upload
  mv -- "$file" "$file.bak"
  echo "  rename done: $file.bak"
  # Progress output
  printf "[%d/%d] Uploaded: %s (Tags=%s)\n" "$i" "$total" "$fname" "$tags"
done

echo "Done – $i files uploaded (each renamed to *.bak)."
