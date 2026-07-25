# Security Policy

## Supported version

Only the latest published revision maintained by Muhammad Ali Nawaz is supported.

## Reporting a vulnerability

Report vulnerabilities privately to the project owner before public disclosure. Do not include credentials, personal information, or active exploit details in a public issue.

## Security expectations

Production deployments must disable `APP_DEBUG`, use a unique application key, use strong credentials, configure HTTPS, rotate exposed tokens, and run dependency audits. The demo seeder is restricted by code to `local` and `testing` environments and requires explicit opt-in plus an explicitly configured strong password.

## Responsible disclosure

Allow reasonable time for a security fix before publishing vulnerability details. Never test against systems or data you do not own or have permission to assess.
