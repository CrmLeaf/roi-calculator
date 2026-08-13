# Changelog

Notable changes to `crmleaf/roi-calculator`.

Format per [Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versioning
per [Semantic Versioning](https://semver.org/spec/v2.0.0.html) - with one extra
rule this package observes, because it computes statutory figures:

> **Any change that alters a published result is at minimum a minor release**,
> and is listed under `Changed` with the notification, circular or Act section
> that prompted it.

## [Unreleased]

## [1.0.0] - 2026-08-12

### Added

- Initial release. Turns "payroll takes us two days a month" into a number a finance director can sign off, with the assumptions visible rather than buried in a vendor slide.

### Statutory basis

- Not a statutory calculation. It values the hours a payroll cycle takes at the loaded cost of the people doing it, nets off the software, and adds back the penalties and corrections that manual processing tends to produce.

[Unreleased]: https://github.com/crmleaf/roi-calculator/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/crmleaf/roi-calculator/releases/tag/v1.0.0
