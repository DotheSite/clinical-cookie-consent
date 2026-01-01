# Install the lightweight pre-commit hook

This repository includes a no-deps pre-commit script at `.githooks/pre-commit`.

To enable it for your local repository, copy it into your git hooks directory and make it executable:

```bash
cp .githooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

- The hook runs `php -l` on staged PHP files.
- If `eslint` is installed on your system, it will also run `eslint` on staged JS files (optional).

If you prefer automated setup for all contributors, consider adding a small setup script or documenting this step in your project onboarding.
