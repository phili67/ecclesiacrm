cd /var/www/CRM
git status --porcelain | sed -E 's/^.. //' | while IFS= read -r f; do
  git add -- "$f"
  git commit -m "update: $f"
done
