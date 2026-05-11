#!/usr/bin/env bash

# Usage:
# ./i18n.sh en "Hello world"

SRC_LANG="$1"
TEXT="$2"

if [[ -z "$SRC_LANG" || -z "$TEXT" ]]; then
    echo "Usage: $0 <en|ru|uk> \"text\""
    exit 1
fi



#
# translate() {
#     local source="$1"
#     local target="$2"
#     local text="$3"

#     curl -s \
#         "https://translate.googleapis.com/translate_a/single?client=gtx&sl=${source}&tl=${target}&dt=t&q=$(python3 -c "import urllib.parse; print(urllib.parse.quote('''$text'''))")" \
#     | awk -F'"' '{print $2}'
# }
#



translate() {
    local source="$1"
    local target="$2"
    local text="$3"

    local encoded
    encoded=$(python3 -c '
import urllib.parse,sys
print(urllib.parse.quote(sys.argv[1]))
' "$text")

    curl -s \
        "https://translate.googleapis.com/translate_a/single?client=gtx&sl=${source}&tl=${target}&dt=t&q=${encoded}" \
    | python3 -c '
import json,sys
data=json.load(sys.stdin)
print("".join(part[0] for part in data[0]))
'
}



# Определяем целевые языки
EN=""
RU=""
UK=""

case "$SRC_LANG" in
    en)
        EN="$TEXT"
        RU=$(translate en ru "$TEXT")
        UK=$(translate en uk "$TEXT")
        ;;
    ru)
        RU="$TEXT"
        EN=$(translate ru en "$TEXT")
        UK=$(translate ru uk "$TEXT")
        ;;
    uk)
        UK="$TEXT"
        EN=$(translate uk en "$TEXT")
        RU=$(translate uk ru "$TEXT")
        ;;
    *)
        echo "Unsupported language: $SRC_LANG"
        exit 1
        ;;
esac

OUTPUT="${EN} | ${RU} | ${UK}"

echo "$OUTPUT"

# clipboard auto-detect
if command -v wl-copy >/dev/null 2>&1; then
    echo -n "$OUTPUT" | wl-copy
elif command -v xclip >/dev/null 2>&1; then
    echo -n "$OUTPUT" | xclip -selection clipboard
elif command -v xsel >/dev/null 2>&1; then
    echo -n "$OUTPUT" | xsel --clipboard --input
elif command -v pbcopy >/dev/null 2>&1; then
    echo -n "$OUTPUT" | pbcopy
elif command -v clip.exe >/dev/null 2>&1; then
    echo -n "$OUTPUT" | clip.exe
fi

