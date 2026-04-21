# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in this project, **please do not open a public GitHub issue**.

Instead, report it privately via GitHub's [Private Vulnerability Reporting](https://docs.github.com/en/code-security/security-advisories/guidance-on-reporting-and-writing-information-about-vulnerabilities/privately-reporting-a-security-vulnerability):

1. Go to the **Security** tab of this repository
2. Click **Report a vulnerability**
3. Fill in the form with as much detail as you can (steps to reproduce, affected versions, potential impact)

## Response Targets

- **Acknowledgement**: within 7 days
- **Initial assessment**: within 30 days
- **Patch / mitigation**: timeline depends on severity, communicated in the advisory

## Supported Versions

This is a foundational example project. Only the `main` branch is actively maintained; older tags are not patched.

## Disclosure Policy

Please give us reasonable time to investigate and release a patch before any public disclosure. Reporters will be credited in release notes unless they prefer to remain anonymous.

## Scope

In scope:
- The application code under `app/`, `config/`, `database/`, `routes/`, and `resources/`
- Default seeders, migrations, and shipped configuration

Out of scope:
- Third-party packages (report upstream to the package maintainer)
- Issues that require physical access to the host
- Self-XSS or social-engineering attacks against the operator

## For Downstream / Forked Projects

If you have forked this project for a derivative system, please:
- Treat the secrets in your derivative project's `.env` as your own responsibility
- Subscribe to this repository's releases to receive security patches
- Re-run `gitleaks` / `trufflehog` on your fork before any public exposure

See `docs/md/00002_開源發佈前衛生檢查.md` for the full hygiene checklist.
