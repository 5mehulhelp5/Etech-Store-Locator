# Changelog — Store Locator

All notable changes to this module. Adheres to [Semantic Versioning](https://semver.org/).

---

## Security: portal-only licensing (removes forgeable key path)

Closes a licensing bypass. Previous versions shipped the HMAC signing secret
inside `LicenseValidator` (`SECRET_FRAGMENTS` / `BUNDLE_SECRET_FRAGMENTS`) and
validated a locally-computed key (`computeKey()` / `computeBundleKey()` via
`checkKey()`) against it, so anyone with the module source could compute a valid
key for their own domain and run the module unlicensed. Secondary bypasses — a
`production_environment=No` toggle and a development-host bypass — are removed too.

### Changed (security)

- Validation is now **portal-only**. `isValid()` honours a key only when the
  ETechFlow portal confirms it; the module ships no signing secret.
- Offline grace derives solely from a cached genuine portal success, never from
  admin-settable config, so it cannot be fabricated.
- `isProductionEnvironment()` is hardcoded to `true`; the sandbox toggle and the
  dev-host bypass no longer short-circuit licensing.
- Rewrote the unit suite, including a hard test that a forged `SP-` key with
  attacker-controlled config and no portal is rejected.
