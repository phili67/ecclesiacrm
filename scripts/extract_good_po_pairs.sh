#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   scripts/extract_good_po_pairs.sh [output_dir]
#
# Defaults:
#   output_dir = locale
#
# Sources scanned:
#   src/locale/textdomain/fr_FR/LC_MESSAGES/messages.po
#   locale/JSONKeys_JS/fr_FR/js-strings.po
#
# Output files:
#   <output_dir>/messages_to_translate.txt
#   <output_dir>/js-strings_to_translate.txt

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR_REL="${1:-locale}"
OUTPUT_DIR="${ROOT_DIR}/${OUTPUT_DIR_REL}"

MESSAGES_PO="${ROOT_DIR}/src/locale/textdomain/fr_FR/LC_MESSAGES/messages.po"
JS_STRINGS_PO="${ROOT_DIR}/locale/JSONKeys_JS/fr_FR/js-strings.po"

for f in "$MESSAGES_PO" "$JS_STRINGS_PO"; do
  if [[ ! -f "$f" ]]; then
    echo "Fichier introuvable: $f" >&2
    exit 1
  fi
done

mkdir -p "$OUTPUT_DIR"

extract_po_entries_to_fix() {
  local input_file="$1"
  local output_file="$2"

  awk '
function reset_entry() {
  fuzzy = 0
  in_msgid = 0
  in_msgstr = 0
  msgid = ""
  msgstr = ""
}

function extract_quoted_value(line,    s) {
  s = line
  sub(/^[^\"]*\"/, "", s)
  sub(/\"[[:space:]]*$/, "", s)
  return s
}

function unescape_po(s,    out) {
  gsub(/\\n/, "\n", s)
  gsub(/\\r/, "\r", s)
  gsub(/\\t/, "\t", s)
  gsub(/\\\"/, "\"", s)
  gsub(/\\\\/, "\\", s)
  return s
}

function escape_po(s,    out) {
  gsub(/\\/, "\\\\", s)
  gsub(/\"/, "\\\"", s)
  gsub(/\n/, "\\n", s)
  gsub(/\r/, "\\r", s)
  gsub(/\t/, "\\t", s)
  return s
}

function flush_entry(    out_id, out_str, should_keep) {
  # We keep only entries to fix:
  # - fuzzy OR msgstr empty
  # - and msgid must be non-empty (skip PO header)
  should_keep = (msgid != "") && (fuzzy || msgstr == "")

  if (should_keep) {
    out_id = escape_po(msgid)
    out_str = ""

    print "msgid \"" out_id "\""
    print "msgstr \"" out_str "\""
    print ""

    kept_count++
  } else {
    skipped_count++
  }
}

BEGIN {
  kept_count = 0
  skipped_count = 0
  reset_entry()
}

{
  if ($0 ~ /^[[:space:]]*$/) {
    flush_entry()
    reset_entry()
    next
  }

  if ($0 ~ /^#[[:space:]]*,[[:space:]]*fuzzy/) {
    fuzzy = 1
    next
  }

  if ($0 ~ /^#/) {
    next
  }

  if ($0 ~ /^msgid[[:space:]]+\"/) {
    in_msgid = 1
    in_msgstr = 0
    msgid = unescape_po(extract_quoted_value($0))
    next
  }

  if ($0 ~ /^msgstr[[:space:]]+\"/) {
    in_msgid = 0
    in_msgstr = 1
    msgstr = unescape_po(extract_quoted_value($0))
    next
  }

  # Skip plural forms in this extractor
  if ($0 ~ /^msgid_plural[[:space:]]+\"/ || $0 ~ /^msgstr\[[0-9]+\][[:space:]]+\"/) {
    in_msgid = 0
    in_msgstr = 0
    next
  }

  if ($0 ~ /^\"/) {
    if (in_msgid) {
      msgid = msgid unescape_po(extract_quoted_value($0))
    } else if (in_msgstr) {
      msgstr = msgstr unescape_po(extract_quoted_value($0))
    }
    next
  }
}

END {
  flush_entry()
  printf("Paires extraites (a corriger): %d\n", kept_count) > "/dev/stderr"
  printf("Entrees ignorees            : %d\n", skipped_count) > "/dev/stderr"
}
' "$input_file" > "$output_file"
}

MESSAGES_OUT="${OUTPUT_DIR}/messages_to_translate.txt"
JS_STRINGS_OUT="${OUTPUT_DIR}/js-strings_to_translate.txt"

extract_po_entries_to_fix "$MESSAGES_PO" "$MESSAGES_OUT"
extract_po_entries_to_fix "$JS_STRINGS_PO" "$JS_STRINGS_OUT"

echo "Fichiers generes:"
echo "- $MESSAGES_OUT"
echo "- $JS_STRINGS_OUT"
