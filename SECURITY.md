# Security Policy

## Supported Versions

This project does not yet have tagged releases. Security fixes are applied to
the `main` branch, which is what should be deployed.

## Reporting a Vulnerability

If you discover a security vulnerability in PrimeUnits, please **do not**
open a public GitHub issue.

Instead, report it privately by emailing **jaeturma@gmail.com** with:

- A description of the vulnerability and its potential impact
- Steps to reproduce it, including any relevant requests/payloads
- The affected file(s), route(s), or commit, if known

You should expect an initial response within 5 business days. We'll work with
you to understand and confirm the issue, and let you know when a fix has
landed. Please give us a reasonable amount of time to address the issue
before disclosing it publicly.

## Scope

This project handles user accounts, seller/dealer verification, listings,
payments, and financing applications. Reports involving authentication,
authorization (RBAC/permissions), payment or transaction handling, and
exposure of personal or financial data are especially appreciated.

Out of scope: vulnerabilities in third-party dependencies that already have a
public CVE and upstream fix — please report those upstream instead, though a
heads-up is still welcome if PrimeUnits looks exploitable in the meantime.
