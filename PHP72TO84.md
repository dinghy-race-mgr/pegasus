# Pegasus PHP 7.2 to PHP 8.4 migration plan

Status: architectural plan with P0.1–P0.3 and P1.1–P1.4 complete; P0.4 carries known data/environment actions and P1.2 records dependency owner actions for later routes. No application PHP changes have been made for this migration. Basic Auth on the live PHP 7.2 Pegasus handler was a temporary P2PShell mitigation, not an application authentication requirement. PHP 8.4 is patched against P2PShell and does not need that temporary Basic Auth control. The first landing-page route is configured and verified locally on PHP 8.4 but has not been pushed or deployed live. Update the decision log, phase checklist, evidence, and open questions in this file as work proceeds.

## Scope and operating rules

- Target: move Pegasus directly from PHP 7.2 to PHP 8.4 at runtime, starting with `rm_utils` (the requested `.rm_utils` area). Preserve URLs, visible UI, database schema and data model, and existing business behaviour.
- Exclude `rm_admin/` and `testing/` entirely from scans, edits, and migration acceptance criteria. Do not edit `common/oss/`. Record any required change there as an owner action.
- During implementation, obtain permission before changing **any non-PHP file**, including Caddy, Compose, Dockerfile, INI, JSON, JavaScript, or test configuration. The request to create and maintain **this specific Markdown plan** authorises its initial creation and ongoing progress updates.
- `common/` outside `common/oss/` is in scope because `rm_utils` includes its classes, libraries, and templates. Shared-code changes need regression checks against other in-scope callers.
- No database migration or schema change is planned. Run write-path tests only against an isolated, anonymised disposable MySQL 8.0 clone; do not run destructive tests against live data.
- Keep changed application PHP compatible with **both 7.2 and 8.4** until the entire Pegasus cutover and rollback window are closed. Avoid 8.x-only syntax and APIs in the first pass. Review each automated rewrite for this condition.

## Evidence and provisional decisions

| Item | Observation / decision |
| --- | --- |
| Source size | P0.1 found 211 PHP files outside `rm_admin/`, `testing/`, and `common/oss/`; `rm_utils/` contains 53 and `common/` excluding `common/oss/` contains 34. No top-level Composer manifest or lock file was found. The earlier count of 212 included a transient untracked `rm_sailor/test_versions.php`; it was absent at P0.1 and no action was taken on it. |
| Runtime | Running containers on this PC: `syc-fpm72` PHP 7.2.34, `syc-fpm84` PHP 8.4.27, Caddy 2.11.7, MySQL 8.0.44. Both PHP containers mount the same website volume. Their runtime `php -m` output includes `mbstring`, `mysqli`, `pdo_mysql`, `curl`, `imagick`, and `apcu`. PHP 7.2 alone has `ftp` and `mcrypt`; PHP 8.4 adds `exif`, `gd`, `intl`, `ldap`, `memcached`, `pdo_pgsql`, `pgsql`, `random`, and `redis`. Effective FPM sessions use private `/tmp` storage in each container and are not shared. |
| Current routing | At P0.1, `C:/syc-software/syc-server-docker/caddy/Caddyfile` defined `@php84` with only `/pegasus_php83/rm_sailor/*`; a separate `/pegasus*` handler sent Pegasus to `syc-fpm72`. The 7.2 block applied temporary Basic Auth as a P2PShell mitigation while the patched 8.4 block did not. During P0.4 the owner removed Basic Auth from the local PHP 7.2 route to permit baseline capture. Authentication parity is not a PHP 8.4 migration requirement, but both handlers must retain equivalent sensitive-file/log blocking. |
| Deployment | `compose.yaml` defines both FPM services on the same web-content mount. `compose-dev.yaml` uses Compose watch from `WATCH_SOURCE_CODE_ROOT_DIR` to `WATCH_TARGET_CODE_ROOT_DIR`, with the Caddy container sharing the content volume. Caddy and the FPM containers must resolve exactly the same script path. |
| Concrete code risks | `common/classes/db_class.php` uses `new mysqli(...)`, then inspects `connect_errno` and `$this->link->error`; PHP 8.1+ MySQLi defaults to exceptions. `rm_utils/dtm_status_synch.php` and `rm_utils/rota_synch_webcollect.php` also use MySQLi directly. `common/classes/db.php` already requests PDO exceptions. `rm_utils` uses sessions, generated files, CSV import/export, cron, remote APIs, email, and results publishing: these need behaviour tests, not just lint. |
| Dependency model | `rm_utils` calls shared code and bundled libraries. `common/oss/` includes phpseclib, PHPMailer, WebCollect and multiple Bootstrap generations: the landing page uses Bootstrap 5.3.2 and Bootstrap Icons 1.11.3, while many template-driven utilities use Bootstrap 3.4.1. Inventory and test these dependencies read-only. `rm_utils/lib/` also contains local WebCollect PHP. There is no evidence yet of Composer-managed application dependencies. |
| Direct jump | A direct **runtime** switch is feasible; PHP does not require an intermediate deployed runtime or database step. It is conditional on the audited code, extensions, sessions, and bundled dependencies passing PHP 8.4 tests. If a blocker cannot be fixed while preserving rollback and scope, hold that route on 7.2 and log the specific owner action. PHP 7.4/8.0/8.1/8.2/8.3 can be used as diagnostic lint/runtime waypoints, not mandatory production stages. |

### P0.1 baseline evidence — 2026-10-27 15:03 UTC

The checks in this section were read-only. Raw `docker inspect`, Compose, environment, log, and configuration output was not written to the repository. The tables below contain only non-secret identifiers and selected effective settings.

#### Source and infrastructure state

| Repository | Branch / commit | Status at capture |
| --- | --- | --- |
| Pegasus `C:/xampp/htdocs/pegasus` | `php84migration` / `e7ae1ff80e509b562f67a028d6328218c564f6ef` | Only `PHP72TO84.md` was untracked. No application file was modified. |
| Docker `C:/syc-software/syc-server-docker` | `main` / `2f6f328c3f325513ffc57028cb38059f1b5e5d7d` | Pre-existing user changes: `.env` and `caddy/Caddyfile` modified, plus unrelated IDE/prompt files. Relevant Compose, Dockerfile, PHP ini, and pool files were clean. The active Caddyfile matches the modified working copy, not necessarily the committed `main` version. |

The local `rm_utils/index.php` and the copies visible to Caddy, PHP 7.2, and PHP 8.4 all had SHA-256 `068bef4d7209afb552047be6d0c78fb8947241ba8777a4d296f657c0fea99cfd`. This confirms that all three containers saw the same application bytes at capture time.

| Infrastructure file | SHA-256 at capture |
| --- | --- |
| `compose.yaml` | `5ce030d1193f8d9055f160c03fb3438539cd7f7bcb6e27fbc8d5cfbedb2b7c45` |
| `compose-dev.yaml` | `ba25bc4e7e314e8cca0bc3143b68653ed6f5ae7e49911c6251308ac664a8f75f` |
| Working and active `caddy/Caddyfile` | `1e4f71326ef868b6f7acc1e78d14f1244e8efc7360825dd13484e1a284841787` |
| PHP 7.2 Dockerfile / `php.ini` / `www.conf` | `e6e4ca4e264f279c066b8917a2c3162d5f6eb716691d73ceb8aa236b8d070086` / `b5b794b0b61803e4a18bdb83d1f7e28aaac1ce522051f016e2dee120db4c68d8` / `1e5051e16a1dd13723c611e2bf5d2e75517bd05cf95619aaa5f46ed5c74096ec` |
| PHP 8.4 Dockerfile / `php.ini` / `www.conf` | `94a2566c0c6a9e28173c1b53dfe0447584967b77fb578ed52c0897f00aa83d25` / `9bc8056fa704ca2211b45c3b633b4685dc4bbda23b50b29862ba461f9f26de3b` / `42692f31044dfd36287b6f5785e69325550dc0d08bae599a41c89c34bd7e9d65` |

#### Running images and Compose

All four core containers were running and healthy. Docker Engine/CLI was 27.4.0 and Compose was 2.31.0. `docker compose -f compose.yaml -f compose-dev.yaml config --quiet` passed. The configured profiles were `dbadmin`, `goaccess`, `logview`, and `sitemonitor`.

| Service | Image tag | Immutable image digest / ID | Effective Compose configuration hash |
| --- | --- | --- | --- |
| `syc-fpm72` | `registry.starcrossyc.org.uk/syc-fpm:7.2-15` | `sha256:f5ee71513e260c48d3a0b8f67ddcd95e34e42a61bb1047d009f692b52fd50c9e` | `2da659a7ba07e6c846c821503aef03dd97fd28f34f4b75156297a7e82b82091d` |
| `syc-fpm84` | `registry.starcrossyc.org.uk/syc-fpm:8.4-04` | `sha256:51d1462f5ef2698c626190847892082052d30f954c06c74119e898ef5b47c3e8` | `64becb598c67e648e6844c920c297c2a52c29f5a451f53dfb1562edb0cf6d761` |
| `syc-webserver` | `registry.starcrossyc.org.uk/syc-caddy:2.11.4-10` | `sha256:3ee254d1325afc23c5825e514513592d90c3f2b3752173d905c89a20cd853234` | `c42e755386ae33a663029b364d0763d82235596213b169bbf7aedea90871e0c4` |
| `syc-mysqldb80` | `registry.starcrossyc.org.uk/syc-mysql:8.0.44-02` | `sha256:c52634cea2dc08800325577f1b000774ae687441b9ca0b0aeac4845d1969a73f` | `1c021c75a25d81a79e18b130647c3aff77437972ccaffeb3079d5b448f88ac7d` |

Caddy and both FPM containers mount the same `syc-websites-volume` at `/home/sycuser/websites`. Compose watch maps `C:/xampp/htdocs/pegasus` to `/home/sycuser/websites/www.starcrossyc.org.uk/html/non-joomla/pegasus_stx`, excluding the local `data` directory.

#### Effective PHP-FPM comparison

Both FPM configuration tests passed. Both pools listen on port 9000, use `www-data` workers (UID/GID 33), have `clear_env=no`, capture worker output, and allow `.php` and `.phar`. Both use UTF-8, file sessions, the `php` session serializer, `PHPSESSID`, a 1,440-second session lifetime, and effective `error_reporting=4343`. This error mask includes selected errors and warnings but is not `E_ALL`.

| Setting | PHP 7.2.34 | PHP 8.4.27 | Migration consequence |
| --- | --- | --- | --- |
| Timezone | No `date.timezone` value | `UTC` | Confirm application/session timezone setup before output comparison. |
| Memory limit | Unlimited (`-1`) | `128M` | The owner accepts this intentional difference. PHP 8.4 will keep its patched-image value rather than inherit the PHP 7.2 value; test large imports, reports, and publishing against the 128M limit. |
| POST / upload limit | `8M` / `6M` | `32M` / `32M` | The owner accepts this intentional difference. PHP 8.4 will keep its patched-image values rather than inherit the PHP 7.2 limits; test request acceptance at the 8.4 limits. |
| Sessions | Files in container-private `/tmp` | Files in container-private `/tmp` | Sessions do not cross runtimes; seamless route switching is not currently possible. |
| FPM process manager | Dynamic; max 10, start 4, spare 2–5 | Dynamic; max 5, start 2, spare 1–3 | Capacity and latency comparisons must account for different pool sizes. |
| Slow request log | 2-second threshold, configured file | Disabled | PHP 8.4 currently has less slow-request diagnostic coverage. |
| Exception arguments | PHP 7.2 setting unavailable | `zend.exception_ignore_args=On` | PHP 8.4 traces intentionally omit argument values. |

The shared Pegasus files and directories are owned by numeric UID/GID 82, while both FPM pools run workers as UID/GID 33. Read-only checks showed workers can read `rm_utils/index.php`, `tmp/`, `data/`, and `logs/`, but cannot write to the three directories. No permissions were changed. Treat this as an existing environment blocker for write-path tests and utilities until ownership/permission intent is confirmed.

#### Effective Caddy route

Caddy validation passed. Sanitised inspection of the adapted JSON found:

| Matcher | Upstream | Basic authentication in adapted handler | Note |
| --- | --- | --- | --- |
| `/pegasus_php83/rm_sailor/*` | `syc-fpm84:9000` | No | Only current Pegasus-related PHP 8.4 route. The name still refers to PHP 8.3. |
| `/pegasus*` | `syc-fpm72:9000` | Yes | Current general Pegasus handler. |
| `/pegasus*` | None | No | Separate compression-exclusion matcher, not a PHP handler. |

The authentication difference was intentional: Basic Auth protected the vulnerable PHP 7.2 handler from P2PShell, while PHP 8.4 is patched. PHP 8.4 does not require authentication parity. No route was changed during P0.1.

#### P0.1 findings carried forward

1. P0.2 must preserve the exact image digests and the **current modified Caddy working copy**; recording only the Docker repository commit would omit the active routing change.
2. A shared-session design or an explicit re-login policy is required before mixed 7.2/8.4 routing.
3. File ownership prevents both current FPM worker identities from writing to Pegasus `tmp`, `data`, and `logs`; investigate before write-path baseline testing.
4. PHP runtime limits, timezone, pool capacity, and slow-log behaviour must be intentionally aligned or incorporated into acceptance tests.
5. The PHP 8.4 Pegasus handler does not need the PHP 7.2 handler's temporary P2PShell Basic Auth, but it must retain the same sensitive-file and log protections before any `rm_utils` URL is routed to it.

### P0.2 rollback preservation evidence — 2026-10-27

P0.2 is complete under the access and rollback policy confirmed by the owner. The immutable PHP images and exact Caddy configuration have been identified and checked without rebuilding PHP 7.2, changing a tag, pushing an image, writing an archive, or accessing the VPS privately.

#### PHP image preservation

The private registry answered successfully for both tag and immutable-digest lookups. The tag currently resolves to the same top-level OCI digest as the running local image. Rollback instructions must use the digest as the source of truth because tags can move.

| Runtime | Human tag | Immutable registry reference | Registry status |
| --- | --- | --- | --- |
| PHP 7.2 | `registry.starcrossyc.org.uk/syc-fpm:7.2-15` | `registry.starcrossyc.org.uk/syc-fpm@sha256:f5ee71513e260c48d3a0b8f67ddcd95e34e42a61bb1047d009f692b52fd50c9e` | Available by exact digest |
| PHP 8.4 | `registry.starcrossyc.org.uk/syc-fpm:8.4-04` | `registry.starcrossyc.org.uk/syc-fpm@sha256:51d1462f5ef2698c626190847892082052d30f954c06c74119e898ef5b47c3e8` | Available by exact digest |

The PHP 7.2 image is therefore recoverable without rebuilding its EOL base, subject to the VPS retaining registry credentials and network access. An additional offline `docker save` archive would add resilience but is not required to identify the image; creating that non-PHP artifact requires explicit permission and a chosen storage location.

#### Caddy configuration preservation

The deployable Caddy state is reproducible from:

- Docker repository commit `2f6f328c3f325513ffc57028cb38059f1b5e5d7d`;
- committed Caddyfile blob `2d3b7a1d43c05e1686f0ff10f430ffd06f921f9a`;
- the working-copy change that replaces `/pegasus*/aidanstestphp84dir/*` and `/pegasus*/aidanssecondtestphp84dir/*` in `@php84` with `/pegasus_php83/rm_sailor/*`; and
- final Caddyfile SHA-256 `1e4f71326ef868b6f7acc1e78d14f1244e8efc7360825dd13484e1a284841787`.

The active container validates this exact file. Development bind-mounts it, but production does **not** mount `caddy/Caddyfile`: `caddy/Dockerfile` copies it into the Caddy image. A production route change or rollback therefore requires a versioned Caddy image replacement, followed by webserver recreation; a source-file edit and live reload alone is not a durable production procedure.

There is a known registry discrepancy:

| Caddy reference | Digest / status |
| --- | --- |
| Locally running `syc-caddy:2.11.4-10` image | `sha256:3ee254d1325afc23c5825e514513592d90c3f2b3752173d905c89a20cd853234` |
| Current private-registry value of the same tag | `sha256:63a1c3b526c343518016b6e6e7e5e0adf598d9af0335af74e2b07c9fca02b58b` |
| Exact local digest in the private registry | Not available |

The Caddy tag is mutable and no longer identifies the locally running development image. On 2026-10-27 the owner accepted this: rollback will use the image currently held in the private registry, rather than the local development image. Before any canary, build the reviewed migration Caddyfile into a **new versioned image tag**, push it, record its immutable registry digest, deploy that digest to staging, and retain the accepted registry rollback image. This is a non-PHP infrastructure change and needs explicit permission at implementation time.

#### Provisional VPS rollback runbook

The infrastructure README documents image rollback by changing the image entry in the production `.env` and running `docker compose up -d --force-recreate <service>`. The owner has confirmed that the live Compose directory is `/home/sycuser/docker-compose`. Private VPS access will not be supplied; server-side commands in this runbook are instructions for the owner/operator and cannot be independently executed or verified by this migration agent.

For a future `rm_utils` canary, the reviewed procedure is:

1. In `/home/sycuser/docker-compose`, the VPS operator records the effective Compose config hash, running PHP/Caddy image IDs and digests, active Caddyfile checksum, and health state. Do not print `.env` or container environment values.
2. Back up the affected live PHP 7.2 files before replacement, even though the versioned Git copies remain the source of truth. Record the deployed file hashes so local drift is visible and do not include secrets or generated data in the backup.
3. Upload the reviewed PHP 8.4/7.2-compatible versions of only the affected files. Before changing routing, smoke-test that those files still work through the PHP 7.2 handler. No schema rollback is part of this procedure.
4. Build and publish a new `syc-webserver` image containing the reviewed Caddyfile routes for the selected PHP 8.4 URL paths. Increment the Caddy image version by one from the preceding release and record its immutable registry digest.
5. Edit the live `.env` in `/home/sycuser/docker-compose` to select the new `CADDY_IMAGE`, validate the effective Compose model, and recreate only `syc-webserver`. Production does not bind-mount the Caddyfile.
6. Wait for healthy status; verify the selected URL is handled by PHP 8.4, unaffected Pegasus URLs remain on PHP 7.2, the expected P2PShell protection model is in force, and sensitive-file/log blocks, static assets, 5xx/PHP logs, and application behaviour are correct.
7. To roll back routing, restore the preceding versioned Caddy image reference in `.env`, validate the Compose model, and recreate only `syc-webserver`. If necessary, restore the backed-up PHP files after routing is back on 7.2. Do not restore the database as a normal rollback step.
8. Record the start/end time, file hashes, image tags/digests, validation results, and reason for rollback in this document.

Do not use `docker compose down`, remove volumes, rebuild PHP 7.2, or restore the database as part of this rollback.

#### P0.2 completion gates

- [x] PHP 7.2 and PHP 8.4 exact digests are present in the private registry.
- [x] The exact current Caddyfile is reconstructable and validates locally.
- [x] A production-appropriate rollback sequence has been drafted from the actual Compose topology.
- [x] Owner accepted the private registry's current Caddy image as the rollback target despite the local/registry tag discrepancy.
- [x] Private VPS inspection is explicitly out of scope. Public checks on 2026-10-27 returned HTTP 200 for the main site and the expected Basic Auth HTTP 401 for `/pegasus_stx/rm_utils/` and `/pegasus_stx/rm_utils/index.php` without credentials.
- [ ] Preserve the reviewed migration Caddyfile in a new immutable registry image and rehearse its rollback in staging when the first route change is ready. This is a Phase 3/4 release gate, not a blocker to the remaining audit work.

### P0.3 `rm_utils` entrypoint and dependency inventory — 2026-10-27

P0.3 is a static reachability inventory. It does not prove that every branch is used in production; dynamic baselining in P0.4 and later tests must confirm actual behaviour. Scans excluded `rm_admin/` and `testing/`, and treated `common/oss/` as read-only.

#### HTTP and scheduled entrypoints

`rm_utils/` contains 28 root PHP scripts. The broad `/pegasus*` Caddy handler means every root script is technically HTTP-reachable behind Basic Auth; `cron_manager_daily.php` is also intended for scheduled execution. There are another 10 PHP files in `rm_utils/lib/` and 15 in `rm_utils/templates/`; these are loaded components rather than intended entrypoints, but direct HTTP execution is not currently blocked by routing.

| Entrypoint(s) | Purpose and principal inputs | Side effects / risk class |
| --- | --- | --- |
| `index.php` | Landing page; reads `rm_utils/data/util_apps.json` | Read-only HTML; preferred first canary candidate. |
| `check_fleet_allocation.php` | Fleet/race-format report; `pagestate`, `class[]`, `race[]` | Reads class and race configuration. |
| `duty_check.php`, `eventcard.php`, `entry_check.php`, `handicap_numbers.php` | Duty, event-card, entry and handicap reports | Primarily DB reads; event/duty/class/entry dependencies. |
| `duty_spreadsheets.php` | `type`, `start`, `end` | Reads events and writes rota-manager CSV output under programme data. |
| `pursuit_starttimes.php` | Event, PY range, class and interval inputs | Reads class data and writes `tmp/pursuitcfg_<eventid>.json`. |
| `pys_export.php` | PYOnline export | Reads event/race/result/class data; creates downloadable export data and logs. |
| `programmemaker.php` | Date range, uploaded/selected programme rules, `pagestate` | Reads tide/configuration data and writes draft programme CSV. |
| `tide_data_scrape.php` | Input/output filenames and date range | Reads local tide text and writes CSV; filename handling needs security and compatibility review. |
| `berth_synch_webcollect.php` | `pagestate`, report/debug flags | Calls WebCollect and writes/copies berth CSV files. |
| `rota_synch_webcollect.php` | `pagestate`, `dryrun`, contacts/notes | Calls WebCollect and Dutyman DB; non-dry-run deletes/inserts `t_rotamember`. Uses `common/oss/webcollect` read-only dependency. |
| `name_match_webcollect.php` | Matching option and JSON member data | Reads member JSON and `t_competitor`; some options update competitor matching data. |
| `dtm_export.php` | Date/rota selection | Reads programme/duty tables, creates DutyMan event/duty CSV files, deletes older matching exports, and may update duty export state. |
| `dtm_import.php` | Uploaded DutyMan CSV; `init`, `submit`, `apply` states | Moves upload, creates backup CSV, and inserts/updates `t_eventduty`. |
| `dtm_status_synch.php` | `dryrun` | Reads external Dutyman `members`/`duties`; writes `t_dutysync` and `t_eventduty`; may call `website_publish.php` and send Brevo email. High impact. |
| `dtm_confirm_check.php` | `dryrun` | Reads future duties/rota members; sends Brevo reminders and increments `t_eventduty.confirmed_reminders`. High impact. |
| `cron_manager_daily.php` | Hard-coded root plus `data/cron/daily.json` | Writes cron log and invokes configured HTTPS URLs. Current controls call `dtm_status_synch.php` daily, `helloworld.php` on days 4–6, and `dtm_confirm_check.php` on days 6/13/20/27. Not CLI-only. |
| `dbimport.php` | `importtype`, uploaded CSV, `pagestate` | Creates table backup then imports/updates `t_class`, `t_tide`, `t_rotamember`, or `t_event`; rota import can truncate existing rota data. Very high impact. |
| `update_pn.php` | `pagestate`, `dryrun` | Imports handicap data and updates `t_class`. |
| `import_help.php` | No explicit request arguments | Truncates and repopulates `t_help`; destructive despite its innocuous name. |
| `publish_period.php` | Date range and publish/unpublish action | Updates event publication state and can trigger website output. |
| `publish_results.php`, `publish_results2.php` | Event/result status and transfer controls | Rebuild result/result-file data, create result artifacts, and optionally transfer through SFTP. High impact; `publish_results2.php` is the menu/current variant. |
| `website_publish.php` | Date range, report and submit state | Builds programme JSON/files, rotates latest file and transfers via SFTP. High impact. |
| `VRR_merge_race_results.php`, `VRR_merge_series_results.php` | Script-coded virtual-race inputs | Read results and emit SQL insert instructions; verify no execution branch before canarying. |

The menu file lists six “current” applications: `programmemaker.php`, `berth_synch_webcollect.php`, `dtm_export.php`, `dtm_status_synch.php`, `publish_results2.php`, and `race_risk_assessment.php`. The final target does not exist anywhere in the in-scope checkout, so that menu entry is a pre-existing inconsistency. No JSON correction is authorised or required for the PHP migration.

#### Shared PHP dependency boundary

Directly loaded shared PHP is:

- `common/lib/util_lib.php`, `rm_lib.php`, `pursuit_lib.php`, and `results_lib.php`;
- `common/classes/db_class.php`, `db.php`, `template_class.php`, `boat_class.php`, `event_class.php`, `rota_class.php`, `import_class.php`, `tide_class.php`, `pys_class.php`, `raceresult_class.php`, and `seriesresult_class.php`;
- `common/templates/general_tm.php`, `race_results_tm.php`, and `series_results_tm.php`; and
- the corresponding `rm_utils/templates/*.php` renderers and `rm_utils/lib/WebCollect*.php` classes.

Transitive integration paths reach `common/oss/phpseclib` for SFTP and `common/oss/webcollect` from `rota_synch_webcollect.php`. These directories remain immutable. Any PHP 8.4 defect proven there becomes an owner action.

#### Data and external-system boundary

Static SQL and shared-class reachability identified these table groups. Individual entrypoint tests should narrow the list to tables actually touched on each path.

| Boundary | Tables / resources |
| --- | --- |
| Configuration/lookups | `t_ini`, `t_code_system`, `t_code_result`, `t_link`, `t_message` |
| Programme/duties | `t_event`, `t_eventduty`, `t_cfgrace`, `t_cfgfleet`, `t_series`, `t_cfgseries`, `t_rotamember`, `t_dutysync`, `t_tide` |
| Entries/results | `t_entry`, `t_competitor`, `t_class`, `t_race`, `t_racestate`, `t_lap`, `t_result`, `t_resultfile`; result generation can also use archive tables `a_entry`, `a_race`, and `a_lap` |
| Help/import | `t_help` plus the import-selected target table |
| External Dutyman database | `members`, `duties` |

External services and protocols are WebCollect REST (`webcollect.org.uk/api/v1`), Brevo transactional email (legacy `api.sendinblue.com` endpoint in two scripts), direct MySQL access to Dutyman, internal/public HTTPS callbacks, and SFTP publication through bundled phpseclib. No external call or email-producing path may run against production during automated baseline testing.

File effects include system/cron logs, `tmp/uploads`, `tmp/db_backup`, pursuit JSON, DutyMan CSV and backup files, programme/duty CSV, PYOnline exports, generated results/programme JSON, “latest” file rotation, and SFTP transfers. P0.1 already showed current local FPM workers cannot write the main Pegasus `tmp`, `data`, or `logs` directories; write-path tests remain blocked until that development permission issue is intentionally resolved.

#### P0.3 migration ordering from the inventory

1. Landing page `index.php` and static component-loading checks.
2. Read-only reports: fleet allocation, handicap, entry, duty, and event card.
3. Deterministic calculation/export paths that write only disposable files.
4. Imports and database mutations on resettable data only.
5. WebCollect/Dutyman/Brevo integrations with safe endpoints or dry-run controls.
6. Results/programme publication, scheduled URLs, and SFTP last.

`PHP72TO84v1.md` has been removed from the Docker repository's `php72to84` branch. This `PHP72TO84.md` is the authoritative migration plan; the actual front end is Caddy and `rm_admin` remains excluded.

### P0.4 isolated-data, safe-target and PHP 7.2 baseline evidence — 2026-10-27

P0.4 is **in progress**. The work below did not change application code, databases, files in `common/oss/`, or external systems. The owner independently removed local PHP 7.2 Basic Auth; the migration work only observed that revised route.

#### Data snapshot decision

The owner confirms that the Pegasus data requiring protection for this migration, specifically email addresses and telephone numbers, has already been anonymised. This owner assurance supersedes the initial inference from the Docker scripts: `mysql/03-anonymiseJoomlaData.sh` documents only the automated Joomla-user anonymisation, but it is not the complete provenance of the Pegasus data. The current `pegasus_stx` data is therefore accepted as the source for an isolated test snapshot.

A consistent logical snapshot was taken with `mysqldump --single-transaction`, including routines, triggers and events, without altering the source schema:

| Snapshot property | Evidence |
| --- | --- |
| Source | Local MySQL 8.0 `pegasus_stx`, restored from the Docker backup identified as `26_10_27`. |
| Storage | `C:\Users\MarkE\AppData\Local\Temp\pegasus-php84-migration\pegasus_stx_owner_anonymised_20261027.sql.gz`; deliberately outside both Git repositories. Do not commit or publish it. |
| Integrity | gzip validation passed; 57 `CREATE TABLE` statements detected; compressed size 1,600,108 bytes. |
| SHA-256 | `6b62d8e8aa5739aa9935642d6d66d742e517aba8fb160a5031bca857275eb8d5` |
| Cleanup | The temporary copy inside the MySQL container was removed after verification; the host snapshot remains available. |

Before any write-path test, restore this file to a separately named disposable schema, point only the test runtime at that schema, verify the schema name at runtime, and prove the reset by restoring it again. Never run migration write tests against the current `pegasus_stx` source schema. Snapshot contents and row values must not be added to this document or test logs.

#### Safe integration target matrix

| Boundary | Evidence and current policy |
| --- | --- |
| SMTP | `syc-email-relay` is running `rnwood/smtp4dev:3.6.1`; its web UI is published locally on port 8080. It is the intended safe SMTP sink, but the currently configured Pegasus SMTP host does **not** match the relay container name/aliases. Do not run an email-producing path until the routing is corrected and verified. |
| Brevo HTTP API | `dtm_confirm_check.php` and `dtm_status_synch.php` call Brevo directly, so smtp4dev does not intercept them. Do not run those send branches until an authorised stub or explicit no-send configuration is verified. |
| WebCollect | Development edits replace its organisation/token values, but the code still targets the external service. Placeholder credentials are not a stub. Do not execute automated WebCollect calls until a safe endpoint or recorded fixture is agreed. |
| Dutyman MySQL | The configured host is non-loopback and unverified. Do not connect until it is proven to be a disposable test service. |
| SFTP publication | The configured server is non-loopback and unverified. Keep all transfer/publish controls disabled until a local sink or disposable test target is proven. |
| Cron/internal HTTPS callbacks | The effective base URL is loopback/local, but do not invoke callbacks until each downstream action is classified. Invoke target scripts directly for controlled tests where possible. |

No WebCollect, Brevo, Dutyman, SFTP, callback, or email-producing request was made during this baseline.

#### Captured PHP 7.2 baseline

The lowest-side-effect `rm_utils/index.php` entrypoint was executed directly in `syc-fpm72`, from its deployed working directory. It reads the menu JSON and does not require a database or external service.

| Check | Result |
| --- | --- |
| PHP process | Exit code 0; no PHP warning, notice, deprecation, parse error, or fatal error in combined output. |
| Response shape | HTML document with title `raceManager` and 9 `href` attributes. |
| Canonical response fingerprint | Output lines joined with LF and no added trailing LF: 6,017 UTF-8 bytes; SHA-256 `8e9b9dce8799a7453c7ccbd900934d89352fd6af48481328fcc2c68c8e4dad16`. This fingerprint is environment-specific and supplements, rather than replaces, DOM comparison. |
| Local HTTP after owner auth change | `http://localhost/pegasus_stx/rm_utils/` returns HTTP 200 without an authentication challenge. Raw response: 6,104 bytes; SHA-256 `10848f634d123897e5c43e00bbfeabd55a8066ce90282d47d8158ec51e0ea519`. |
| Screenshot | A 1440×1200 Edge capture was visually checked: the existing `raceManager Utilities` page, header, styling and utility cards render without an auth prompt. Stored outside Git at `C:\Users\MarkE\AppData\Local\Temp\pegasus-php84-migration\php72-rm-utils-index-20261027.png`; 119,389 bytes; SHA-256 `33556f75fb219d7049e71d3fb7d61e6bfc97be4801a86f2e6f998a3fdbc60cbb`. |

Five intended read-only report URLs were then probed without submit parameters: `check_fleet_allocation.php`, `duty_check.php`, `eventcard.php`, `entry_check.php`, and `handicap_numbers.php`. Each returned HTTP 200 with the same 2,602-byte application error page and SHA-256 `bee4c5a6fada67c0882ca16de7227ff9880c5b20191a8b3e4600cab98bf99556`; none produced a valid report baseline. The FPM log records two pre-existing environment failures:

- Pegasus system-log writes fail with permission denied; and
- the configured Pegasus database host does not resolve inside `syc-fpm72`, so MySQLi attempts a failed connection and the application error page still returns HTTP 200.

At the time of the failed probes, the repository and deployed `config/common.ini` were byte-identical and both contained `db_host = localhost`. The four fixed-ID `rm_utils` session files created by those probes consequently cached `db_host=localhost`. By contrast, `C:/syc-software/syc-server-docker/.env` contained `DEVEDITS_PEGASUS_DB_HOST=syc-mysqldb80`, the running database container alias.

The owner subsequently changed `config/common.ini` to `db_host = syc-mysqldb80`. The changed value is present in both the checkout and the shared deployed volume, resolves inside `syc-fpm72` to the database container, and a session-free PHP 7.2 connection plus `SELECT 1` succeeded against MySQL 8.0.44. With owner permission, the four test-created sessions caching `localhost` were removed. The repeated requests recreated them with `db_host=syc-mysqldb80`. The deployed and repository `common/classes/db_class.php` files are not byte-identical (the inspected constructor logic is the same, with at least an additional deployed comment block); reconcile deployed/source bytes before code-change comparisons.

The repeated read-only HTTP baselines all returned HTTP 200 with distinct application responses and no PHP diagnostic text in their bodies. No MySQL connection warning, fatal error, or parse error was emitted:

| URL | Bytes | SHA-256 | Structural check |
| --- | ---: | --- | --- |
| `check_fleet_allocation.php` | 13,425 | `cadffc530b80caee12d7364d18e939dafbee6b6e1cc7ea4b95d15c68df25f81b` | Title `fleet allocation`; one form. |
| `duty_check.php` | 6,841 | `f760d8f7f51a95253396dbec2c415ae9e638e7a6b641225ede4b69d2b9bd4e0c` | Title `duty_check`; one form and two inputs. |
| `eventcard.php` | 7,699 | `eb339c32734650e0c0371b35a6cd35a7c85c49c1c01f71c113b1d7029767b489` | Title `eventcard`; one form and nine inputs. |
| `entry_check.php` | 34,717 | `94151e98dc13f2e14cd66fac20d0fe8d54477190d2ef657efde09bb92d515417` | One table; no title element. |
| `handicap_numbers.php` | 26,689 | `fd1819fcd8057f820669d4ff2c1e4bd5b05463ae4be7ee9c9ed9fb30239755bb` | Title `handicaps`; one table. |

The attempted system-log filename is `../logs/sys/sys_2026-10-27.log`, set by `config/rm_utils_cfg.php`. From the `rm_utils` working directory this resolves inside the container to `/home/sycuser/websites/www.starcrossyc.org.uk/html/non-joomla/pegasus_stx/logs/sys/sys_2026-10-27.log`. It is not a direct write to `C:/xampp/htdocs/pegasus`: `/home/sycuser/websites` is the Docker named volume `syc-websites-volume`, whose Docker-VM mountpoint is `/var/lib/docker/volumes/syc-websites-volume/_data`. The target file does not yet exist. `logs/sys` is mode `775`, owned by UID/GID `82:82`, while the FPM workers run as `33:33`, explaining the permission-denied warning.

#### Recommended development permission correction

Both the running PHP 7.2 and PHP 8.4 FPM workers use UID/GID `33:33`. `dev/02-dev-edits.sh` currently changes the entire Pegasus tree to `82:82`, based on an older assumption that PHP 7.2 `www-data` is UID 82. The current images do not follow that assumption.

Use a least-privilege, dual-runtime correction:

1. Leave application PHP, configuration and read-only assets owned as they are; do not make the entire Pegasus tree writable by FPM.
2. In the local development volume only, preserve owner UID 82 but change the group of the required runtime-write roots—initially `logs/`, `tmp/`, and `data/`—to GID 33 (`82:33`). This allows both FPM versions to write through their shared group without changing ownership of the whole application.
3. Set directories to `2775` so new descendants retain the shared group, and ordinary runtime files to `0664`. Preserve executable bits only where they already have a justified use. Do not use `0777`.
4. Add the same targeted correction **after** the broad Pegasus `chown` in `dev/02-dev-edits.sh`; otherwise the next restored development volume will recreate the fault. That shell-script edit and permission mutation require explicit owner permission before implementation.
5. Verify write/create/delete as UID/GID `33:33` from both `syc-fpm72` and `syc-fpm84`, then repeat a read-only request and confirm that the expected log entry is created without FPM warnings.
6. Audit later export/publication paths separately; add another writable directory only when a tested route proves it necessary. Before applying an equivalent production change, verify the production worker UID/GID rather than assuming it is 33.

This corrects the environment mismatch without changing PHP, UI, database data/schema, or `common/oss/`.

A representative export artifact and successful application-log baseline are still outstanding. They require restoration to a resettable test schema before write testing, safe targets for any affected integration, and resolution of the FPM write-permission mismatch. Do not check off P0.4 until those gates are met.

### P1.1 compatibility audit — completed 2026-10-27

The audit boundary in this checkout contains 87 PHP files: 53 under `rm_utils/` and 34 under `common/`, excluding `common/oss/`. `rm_admin/` and `testing/` were not scanned.

#### Syntax gate

The exact checkout contents were streamed to each container's linter without copying or altering files:

| Runtime | Files linted | Parse failures |
| --- | ---: | ---: |
| PHP 7.2.34 (`syc-fpm72`) | 87 | 0 |
| PHP 8.4.27 (`syc-fpm84`) | 87 | 0 |

This proves syntax compatibility only; it does not prove runtime behaviour or absence of deprecations.

#### Checkout/deployed-volume drift

The shared deployed volume is not byte-for-byte equivalent to this checkout and therefore must not be treated as the source audit result. It contains 91 scoped PHP files versus 87 in the checkout:

- deployed only: `rm_utils/dtm_duty_export.php`, `dtm_duty_import.php`, `dtm_event_export.php`, `dutyman_export.php`, and `test_webcollect_client.php`;
- checkout only: `rm_utils/publish_results2.php`; and
- differing bytes: `common/classes/comp_class.php`, `db_class.php`, `email_class.php`, `seriesresult_class.php`, `common/lib/rm_event_lib.php`, `rm_utils/dbimport.php`, `dtm_status_synch.php`, and `templates/layouts_tm_bs5.php`.

Do not draw PHP 8.4 acceptance conclusions from the current deployed-volume responses for a differing file. Reconcile the intended source/version before route comparison; do not copy either side over the other without owner direction.

#### PHPCompatibility result and provenance

With owner permission, temporary tooling was installed outside the repository under `/tmp/pegasus-php84-tools` in `syc-fpm84`: Composer 2.10.3 (official PHAR SHA-256 verified), PHP_CodeSniffer 4.0.4, PHPCompatibility 10.0.0-alpha2 at commit `e0f0e5a3dc819a4a0f8d679a0f2453d941976e18`, and PHPCSUtils 1.2.3. The 87 exact checkout PHP files were copied to a separate `/tmp` scan tree; `common/oss/` was absent. No project Composer/config/vendor file was created.

Command semantics: standard `PHPCompatibility`, `testVersion=7.2-8.4`, PHP extensions only. Result: **4 errors and 28 warnings across 13 files; 0 automatically fixable**. The JSON report is outside Git at `C:\Users\MarkE\AppData\Local\Temp\pegasus-php84-migration\phpcompatibility-7.2-8.4-20261027.json` (19,521 bytes; SHA-256 `468e265c92a1d6a5cf5836c495d24053794be7136ac40f220914adb49900903e`). PHPCompatibility 10 is pre-release and its coverage is not proof of complete compatibility. Sources: [PHPCompatibility package and usage](https://packagist.org/packages/phpcompatibility/php-compatibility), [PHPCompatibility releases](https://github.com/PHPCompatibility/PHPCompatibility/releases), and [PHP_CodeSniffer requirements](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Requirements).

| Finding | Locations | Reachability / priority | Required response and planned test |
| --- | --- | --- | --- |
| Changed `htmlentities()` default flags (4 errors) | `common/classes/db_class.php:564,575`; `dbphpr_class.php:545,555` | `db_class.php` is broadly reachable but only these error/debug log branches are affected. No static `rm_utils` reference to `dbphpr_class.php` was found. Medium priority after MySQLi failure handling. | Pass explicit PHP 7.2-equivalent flags and encoding after confirming intended log representation. A runtime probe confirmed an apostrophe remains `'` on 7.2 but becomes `&#039;` on 8.4 with defaults. Test error/debug log bytes on both versions. |
| CSV escape parameter omitted (19 warnings) | `common/classes/import_class.php:37,64`; `pys_class.php:554,560`; `common/lib/util_lib.php:1538,1544`; `rm_utils/berth_synch_webcollect.php:297,303`; `dtm_export.php:457,463`; `dtm_import.php:455,462,486`; `duty_spreadsheets.php:112,118`; `programmemaker.php:980,1007`; `tide_data_scrape.php:55,128` | Reachable import/export paths; high priority for those batches because PHP 8.4 emits deprecations and future defaults change output/parsing. | Pass the explicit escape argument that preserves current behaviour; compare representative CSV bytes and round-trip parsing on 7.2/8.4, including delimiters, quotes, backslashes, blank fields and line endings. |
| `auto_detect_line_endings` deprecated (3 warnings) | `common/classes/import_class.php:34,60`; `rm_utils/dtm_import.php:480` | Reachable CSV import paths; medium priority. PHP 8.4 runtime probe emits a deprecation. | Preserve 7.2 handling with a version-guarded setting only where required; test LF, CRLF and legacy CR fixtures before removing or guarding it. |
| `E_STRICT` constant deprecated (1 warning) | `rm_utils/lib/functions.php:70` | Reachable through the local WebCollect client used by `berth_synch_webcollect.php`; later integration batch. PHP 8.4 runtime probe confirms the deprecation. | Avoid evaluating `E_STRICT` on PHP 8.4 while retaining 7.2-safe syntax; unit-test `getErrorDescription()` for relevant error levels on both runtimes. |
| Argument-inspection semantics (5 warnings) | `common/lib/rm_lib.php:153`; `common/lib/util_lib.php:783,797,808,854` | The reported behaviour changed in PHP 7.0, before the current PHP 7.2 baseline. The calls occur only while constructing failure diagnostics. Low priority/no 7.2→8.4 delta. | Record as reviewed and defer unless a focused failure-path test shows a 7.2/8.4 difference. |

The text/API scan found no calls to `each`, `create_function`, `money_format`, magic-quotes functions, `__autoload`, `ereg`, legacy `mysql_*`, or `mcrypt_*`, and no curly-brace string/array offsets.

PHPCompatibility does not flag the PHP 8.1 MySQLi default-reporting change. Manual inspection therefore adds the highest-priority behavioural finding: `common/classes/db_class.php:60-61` expects non-throwing connection/error returns, while PHP 8.4 defaults to `mysqli_sql_exception`. The wrapper is broadly reachable. Direct external-database calls also exist in `rm_utils/dtm_status_synch.php:44,255,285` and `rota_synch_webcollect.php:142,146`; `common/classes/dbphpr_class.php:69-70` and `common/lib/db_lib.php` have no static `rm_utils` caller and are currently lower priority. Planned test: valid connection/query plus deliberately invalid host/query on an isolated database, comparing returned/logged behaviour on 7.2 and 8.4 without exposing credentials.

### P1.2 third-party and extension matrix — completed 2026-11-02

`common/oss/` is excluded from application edits and is also entirely ignored by this repository's `.gitignore`; `git ls-files common/oss` returns no files. The local checkout and deployed shared volume currently contain the same top-level dependency directories relevant to this audit, but the Pegasus Git branch alone cannot reproduce them. A release must therefore identify the separate dependency source/artifact and verify its checksums rather than assuming a Git checkout contains the runtime libraries.

#### Reachable PHP dependencies

| Dependency | Provenance and load mechanism | PHP 8.4 evidence | Disposition / owner action |
| --- | --- | --- | --- |
| phpseclib SFTP | Manually included from `common/oss/phpseclib` by `common/lib/util_lib.php` and `results_lib.php`; 24 PHP files. The local `readme.txt` calls it 2.0, but the code exposes the legacy global `Net_SFTP` API, has no Composer/version metadata, and does not match the official 1.0 or 2.0 `Net/SFTP.php` release/branch blobs checked. Treat it as an unversioned/custom snapshot, not a proven upstream release. | All files lint on PHP 7.2 and 8.4. PHP 8.4 loads `Net_SFTP` under `E_ALL` without a diagnostic. Both runtimes provide OpenSSL, GMP, BCMath, mbstring and zlib. No connection or transfer was attempted. | Not a landing-page blocker. Before publication/SFTP routes move, the dependency owner must supply provenance and a versioned artifact or a reviewed upgrade, then run login/upload/download/failure tests against an authorised SFTP target. Do not edit this tree in place. The official project documents maintained 2.0/3.0 branches, and its changelog includes later security fixes: [phpseclib repository](https://github.com/phpseclib/phpseclib), [changelog](https://github.com/phpseclib/phpseclib/blob/master/CHANGELOG.md). |
| WebCollect PHP client | Ten unversioned files under `common/oss/webcollect/lib`; nine `WebCollect*.php` files are byte-identical to the tracked copies in `rm_utils/lib`. `rota_synch_webcollect.php` loads the ignored copy; `berth_synch_webcollect.php` loads the tracked copy. Neither copy contains version/license metadata. Transport uses HTTPS `fopen()` streams and JSON. | All ignored and tracked files lint on 7.2/8.4. PHP 8.4 constructs `WebCollectRestapiClient` under `E_ALL` without a diagnostic or network call. Both runtimes have `allow_url_fopen=1`, a 60-second default socket timeout, OpenSSL and JSON. | Not a landing-page blocker. Before WebCollect routes move, the owner must identify the source/license and canonical copy, then test controlled success, HTTP/authentication failure, malformed JSON and timeout cases. Do not call the production API in automated tests. |
| PHPMailer helper | `common/lib/util_lib.php::u_sendmail()` requires `common/oss/phpmailer/class.phpmailer.php`, and `common/lib/mail_lib.php` instantiates global `PHPMailer`. That directory/file is absent from both checkout and deployed volume. No static `rm_utils` caller of `u_sendmail()` was found; current in-scope email paths call Brevo directly with cURL instead. | A file-existence probe returns missing, so this dormant helper cannot be runtime-tested. The expected filename/API indicate the old pre-6 loading style. | Not a current `rm_utils` blocker, but any future caller is blocked until the owner either restores the exact supported dependency or updates the helper to a maintained namespaced PHPMailer release and tests it on both runtimes. PHPMailer says 5.2 is unmaintained and documents the breaking 6.x loading/API change: [PHPMailer upgrade guide](https://github.com/PHPMailer/PHPMailer/blob/master/UPGRADING.md). |
| Bundled Brevo SDK 2.0.13 | Ignored `common/oss/brevo-php-2.0.13` contains a Composer manifest/lock and Guzzle 6.3.3, but no in-scope PHP references or autoloader load it. `rm_utils` uses direct Brevo HTTP calls instead. | Not loaded or tested because it is unreachable from the audited paths. | Do not treat this directory as an application dependency or update it during this migration. Re-audit only if a caller is introduced. |

The lint set contained 44 reachable files: 24 phpseclib, ten ignored WebCollect, and ten tracked `rm_utils/lib` files. It passed without syntax errors on PHP 7.2.34 and PHP 8.4.27. These load checks do not prove protocol compatibility or safe production side effects.

#### Browser dependencies and packaging findings

| Dependency | Exact local identification | Route relevance / action |
| --- | --- | --- |
| Bootstrap | 3.4.1 and 5.3.2; Bootstrap Icons 1.11.3 | Preserve both UI generations and their route-specific baselines. Bootstrap 3.4.1 is the final 3.x release and Bootstrap 3 is end-of-life; replacement is a separate UI/security project, not a PHP migration edit: [Bootstrap 3.4 documentation](https://getbootstrap.com/docs/3.4/getting-started/). |
| jQuery | 1.11.0 | Loaded by Bootstrap 3 templates. Preserve for parity during migration and assess replacement separately with the Bootstrap 3 UI. |
| Bootstrap Growl | Package metadata identifies 0.0.1 | Loaded by shared and `rm_utils` Bootstrap 3 templates; preserve and include in route asset checks. |
| FormValidation | Bundled files identify 0.6.1 (with older 0.5.0 files also present) | `layouts_tm.php` and `pys_export_tm.php` request the 0.6.1 `dist` paths; preserve and exercise validation behaviour on representative forms. |

#### Effective extension matrix

| Capability | In-scope use | PHP 7.2 | PHP 8.4 | Result |
| --- | --- | --- | --- | --- |
| `mysqli`, `mysqlnd`, `PDO`, `pdo_mysql` | Main and Dutyman database paths | Present | Present | Extension parity passes; MySQLi exception-mode behaviour remains a P2 code/test item. |
| `curl` and `json` | Brevo, callbacks, cron and JSON data | Present | Present | Extension parity passes. External endpoints remain blocked until safe targets exist. |
| `openssl`, `mbstring`, `gmp`, `bcmath`, `zlib` | phpseclib and shared text/data handling | Present | Present | Required/accelerating capabilities are available on both runtimes. |
| URL streams | WebCollect HTTPS client | `allow_url_fopen=1`, timeout 60s | `allow_url_fopen=1`, timeout 60s | Effective setting parity passes; controlled network behaviour still requires testing. |
| `session` | Utility state | Present | Present | Module parity passes, but container-private session storage remains a separate environment decision. |
| `ftp`, `mcrypt` | PHP 7.2 image only | Present | Absent | No active in-scope `ftp_*` or `mcrypt_*` call was found. Commented FTP code and SFTP configuration labels do not create an extension requirement. |

P1.2 is complete as an inventory and supportability gate. Its owner actions are route-specific: they hold SFTP publication, WebCollect, dormant PHPMailer, and affected Bootstrap 3 routes, but they do not block the already selected Bootstrap 5 landing-page canary.

### P1.3/P1.4 first route selection and local Caddy evidence — 2026-11-02

The first canary is the read-only landing page at `/pegasus_stx/rm_utils`, `/pegasus_stx/rm_utils/`, and `/pegasus_stx/rm_utils/index.php`. It loads `rm_utils/index.php`, `common/lib/util_lib.php`, `config/common.ini`, and `rm_utils/data/util_apps.json`; its visible dependencies are the favicon, Bootstrap 5.3.2, and Bootstrap Icons 1.11.3. It does not open a database connection, write files, send mail, or call an external API. The audited checkout and deployed-volume drift list do not identify `index.php` or `util_lib.php` as differing PHP files. No application PHP change is required for this first route.

This Bootstrap dependency is route-specific, not application-wide. `common/templates/general_tm.php`, `rm_utils/templates/layouts_tm.php`, and `common/lib/util_lib.php` load the bundled `common/oss/bootstrap341` assets. Consequently, template-driven utilities including WebCollect synchronisation, fleet/duty/event/handicap reports, DutyMan import/export, programme generation, publishing, database import, pursuit starts, PY export, and handicap updates use Bootstrap 3.4.1. `pys_export_tm.php` and `publishresults_tm.php` also reference Bootstrap 3.4.1 directly. The later Caddy route batches must include the `bootstrap341` static asset paths required by those pages and verify their Bootstrap 3 UI separately; they must not replace or restyle these pages as part of the PHP migration.

The owner authorised migration infrastructure changes on `origin/php72to84`. Inspection found `C:/syc-software/syc-server-docker` already on local `php72to84` at the same commit as `origin/php72to84`; no tracked `main` changes remained to discard. The existing development-only `.env` watch-path adjustment was preserved, and the unrelated untracked `.idea/` directory was not touched.

The Caddy matcher now sends only the three landing-page forms and their required static asset paths to `syc-fpm84`; other `rm_utils` PHP remains on the authenticated PHP 7.2 fallback. The local Caddy image revision was incremented from `syc-caddy:2.11.4-10` to `syc-caddy:2.11.4-11`, built from Caddy 2.11.7, and used to recreate only `syc-webserver`. The image is local and has not been pushed or deployed to live.

Validation evidence:

- merged development Compose configuration passed `config --quiet`;
- the active Caddyfile passed `caddy validate` and adapted successfully;
- `syc-webserver` became healthy on local image ID `sha256:8b56f8fc3b221ab69219dc44b07b35e7f50cb7372b0d4354fb8a1e9b0d210f90`;
- `/pegasus_stx/rm_utils` redirected permanently to the slash form; both the slash form and explicit `index.php` returned HTTP 200 and 6,104 bytes without Basic Auth;
- the PHP 8.4 FPM access log recorded the two canary requests as `GET /pegasus_stx/rm_utils/index.php` with HTTP 200, while the PHP 7.2 log recorded no canary request;
- the selected Bootstrap CSS paths returned HTTP 200; and
- unselected `/pegasus_stx/rm_utils/helloworld.php` returned the expected HTTP 401 from the PHP 7.2 fallback.

This completes route selection and the currently authorised non-PHP implementation for the first local canary. It does not complete production P3/P4 gates: the image still needs registry publication, immutable-digest capture, and the live operator procedure below.

## 1. Compatibility and breaking-change risk assessment

### Highest-priority changes for this application

| Risk | PHP change and likely effect | Initial response |
| --- | --- | --- |
| Fatal runtime errors | PHP 8.0 turns some invalid operations and internal-function arguments into `TypeError`, `ValueError`, or `Error`: `count(null)`, arithmetic on nonnumeric strings, invalid array/string offsets, writing a property on a non-object, too few arguments, and undefined unqualified constants. Nested ternaries without parentheses and curly-brace offsets cease to parse. | Lint all in-scope PHP with 8.4; inspect values at external input, SQL/CSV/API boundaries; fix invalid values explicitly. No ini switch restores 7.2 behaviour. |
| Changed comparisons and result order | PHP 8.0 changes number versus nonnumeric-string loose comparisons (`0 == ''` and `0 == 'abc'` become false). Sorts are stable when comparison returns equality; old tie ordering can change. Boolean `usort` comparators are invalid for reliable ordering. This can affect handicaps, race results, identifiers, and form controls. | Search risky `==`, `!=`, `<`, `>`, `usort`, and `array_search` sites; specify intended coercion and tie-breaks; compare representative output on both runtimes. No global compatibility switch. |
| Diagnostics versus failure | Reads of undefined variables, keys, or properties generally become **warnings** in 8.0, rather than notices; they do **not generally become exceptions** by themselves. Creating dynamic properties is deprecated in 8.2. Passing `null` to many nonnullable internal parameters is deprecated from 8.1. | Treat staging warnings as evidence of possible missing data. Fix important paths; temporarily suppress only known nonfatal diagnostics in production after measuring them. Do not conflate warning suppression with error recovery. |
| MySQLi | Since 8.1 the default report mode throws `mysqli_sql_exception`. `db_class.php` expects error returns, so its error path may be bypassed. | Prefer updating the wrapper to handle exceptions consistently. For the first pass, `mysqli_report(MYSQLI_REPORT_OFF)` **before connections** restores the former report mode; test both direct MySQLi callers. Keep PDO's explicit `ERRMODE_EXCEPTION` unchanged. |
| Function, signature, and syntax removals | In the 7.2–8.4 span, removed functionality includes `each()`, `create_function()`, `money_format()`, `get_magic_quotes_*()`, `__autoload()`, reversed `implode()` arguments, `parse_str()` without a result argument, curly-brace offsets, PHP 4-style constructors, and static calls to nonstatic methods. `ereg` and old `mysql_*` were removed **before PHP 7.2**; only flag them if dead code or bundled libraries still contain them. | Use PHPCompatibility/Rector for discovery, then make targeted 7.2-compatible fixes. Verify the exact replacement semantics; for example, `number_format()` is not a drop-in locale equivalent for `money_format()`. |
| String, HTML, and CSV output | `htmlspecialchars()` defaults changed in 8.1 to include `ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401`; invalid UTF-8 and apostrophe escaping can change HTML bytes or downloaded files. PHP 8.4 tightens CSV argument validation. Locale defaults and numeric formatting can affect reports. | Pass explicit flags/encoding where output parity matters; compare canonical HTML text/DOM, CSV, JSON, and PDFs/exports. Do not edit UI assets. |
| Dependencies and extensions | The 7.2 runtime has PECL `mcrypt`; 8.4 image lacks it. `mcrypt` is not necessarily used by in-scope code; verify calls and transitive libraries. PHP 8.4 moves IMAP, OCI8, PDO_OCI and PSpell out of core; none appears in the current runtime extension lists. OpenSSL/PCRE/library behaviour may also change. | Build a route-to-library-to-extension map. Do not add an extension simply because it exists on 7.2. If `common/oss` code needs repair, write an owner ticket with file, failure, proposed change, and test evidence. |
| 8.4-specific issues | Implicitly nullable typed parameters (`T $x = null`) are deprecated in 8.4; `exit()` now coerces differently and may throw for invalid values; several internal calls validate inputs more strictly. `E_STRICT` is removed as an error level and the constant is deprecated. | Audit after the fatal paths; use `?T` where possible while preserving 7.2 syntax. Avoid `E_STRICT` in new settings. |

`declare(strict_types=1)` is opt-in per file: PHP 8.4 has **not** globally switched all application PHP to strict scalar typing. There is no setting that makes PHP 8.4 use 7.2 comparison, type-error, or removed-function rules. PHP 8's named-argument interpretation of string keys in `call_user_func_array()` can affect dynamic dispatch; use `array_values()` only when positional calls are intended.

### Limited compatibility controls

1. In staging, use `display_errors=Off`, `log_errors=On`, and `error_reporting=E_ALL` so browser markup stays clean while warnings and deprecations are recorded. Check effective **FPM** settings, because the image has both `php.ini` and a later logging override. Mask secrets and request data in any logs.
2. For a time-limited production canary, choose an explicit `error_reporting` mask for known warnings/deprecations only after staging has classified them. This reduces log/output noise; it cannot suppress `TypeError`, `Error`, parse errors, or changed results. Do not globally turn off all warnings as a substitute for testing. Restore full reporting after remediation.
3. If MySQLi wrapper repair would delay the first utility, invoke `mysqli_report(MYSQLI_REPORT_OFF)` before **every** relevant connection, by a shared PHP bootstrap or an 8.4-only prepend hook. Verify the effective FPM setting; plan to remove this shim once callers use explicit error handling. Such a bootstrap/config edit needs the user's permission if it touches a non-PHP file.
4. Compare PHP 7.2 and 8.4 `php -m`, `php --ini`, `php -i`, FPM pool values, loaded configuration, timezone, charset, session settings, upload limits, and filesystem access. Align only settings required for Pegasus; do not assume the Joomla 8.4 defaults suit Pegasus.

References: [PHP 8.0 incompatibilities](https://www.php.net/manual/en/migration80.incompatible.php), [PHP 8.1 MySQLi change](https://www.php.net/manual/en/migration81.incompatible.php), [PHP 8.1 deprecations](https://www.php.net/manual/en/migration81.deprecated.php), [PHP 8.2 deprecations](https://www.php.net/manual/en/migration82.deprecated.php), [PHP 8.4 incompatibilities](https://www.php.net/manual/en/migration84.incompatible.php), [PHP 8.4 deprecations](https://www.php.net/manual/en/migration84.deprecated.php), [PHP 8.4 moved extensions](https://www.php.net/manual/en/migration84.removed-extensions.php).

## 2. Automation and tooling strategy

Tooling should run in an isolated, pinned PHP 8.4 CLI environment outside deployed Pegasus content. Because there is no application Composer manifest, first inventory manual dependencies; then, if tooling requires Composer, use a separate tooling manifest and lock file **after permission for those non-PHP files**. Do not create `vendor/` under the served tree or scan/edit excluded directories. Keep reports outside web content and redact credentials.

Recommended sequence for each small batch:

1. Record a clean Git baseline. Run PHP 7.2 and 8.4 syntax lint on `rm_utils/`, `common/` excluding `common/oss/`, and required config PHP. Record parse failures first. Keep the untouched `rm_sailor/test_versions.php` out of the baseline.
2. Run PHP_CodeSniffer with PHPCompatibility as a **report-only** version scanner, range `7.2-8.4`; additionally run a 7.2 compatibility check after changes. Choose and pin a version that supports the needed 8.4 checks. As of the referenced release list, 10.0 is pre-release, so document any coverage gaps and do not treat a clean scan as proof. Standard PHPCS style rules are optional; avoid mass formatting and `phpcbf` in the migration.
3. Configure PHPStan on only the same source roots, with explicit PHP 8.4 target (and, if useful, a separate PHP 7.2 compatibility job), modest initial level such as 0–2, bootstrap/stubs for session data and manual libraries, and a **new-findings** gate. Baseline existing findings instead of suppressing the entire directory. Psalm is an alternative or second opinion for complicated flows, not a mandatory duplicate gate.
4. Configure Rector with only selected compatibility rules. Use the 8.4 PHP set/level in **dry run for discovery**, but set generated syntax to PHP 7.2 while dual compatibility is required. Rector's PHP sets may introduce 8.x syntax when the target is 8.4, so do not apply the whole set blindly. Pin Rector, review its current configuration API, apply a narrow rule group to one module at a time, inspect the diff, and rerun **both** 7.2 and 8.4 lint. Do not run Rector on `common/oss/`, `rm_admin/`, `testing/`, generated outputs, or unrelated style code.
5. Rerun PHPCompatibility and PHPStan, then exercise the changed workflow. Commit a small, reversible PHP-only batch with its diagnostic delta and test evidence. Move to the next batch only when output and side effects match.

References: [Rector PHP sets](https://getrector.com/documentation/set-lists), [Rector PHP version configuration](https://getrector.com/documentation/config-configuration), [PHPCompatibility release status](https://github.com/PHPCompatibility/PHPCompatibility/releases), [PHPStan configuration](https://phpstan.org/config-reference).

## 3. Testing and UI parity

There is no goal to build a broad unit suite. Build a small, repeatable acceptance matrix around **actual utilities and shared paths**. Add integration tests when practical, especially for DB calls, CSV import/export, email through the local capture service, generated files, and external API adapters with controlled fixtures. Add focused unit tests only for changed coercion, sorting, parsing, or formatting logic. Any non-PHP test/config file needs prior permission.

For each selected utility, record: exact URL and input, session and expected access-control state, source dataset, expected HTTP status/redirect, visible text/DOM, output file bytes or structured records, outbound email/API requests, database rows affected (on the isolated clone), logs, and 7.2 versus 8.4 result. Pin the same code commit and seed data for both runtimes. Reset the disposable clone between tests that write; avoid side effects from scheduled jobs. Do not compare random session IDs, timestamps, CSRF values, or ordering that is intentionally nondeterministic as raw bytes.

Minimum `rm_utils` coverage: landing page and navigation; one read-only report; programme/event generation; result publication including equal-score ties; CSV import/export with invalid and valid rows; duty/rota operations; WebCollect/Brevo/API failure and success handling; file creation and permissions; session continuation across both FPM versions. Test failure paths such as missing data, malformed requests, unavailable DB/API, and rejected upload sizes.

Use Playwright for representative UI journeys where PHP changes affect rendered pages: capture baseline screenshots at fixed viewport, timezone, browser, fonts and seed data; compare screenshots plus DOM text, form values, links, network/console errors, and downloaded content. Treat visual diffs as review items, not automatic defects; require human review for intentional dynamic content. No template styling, JS, CSS, or user-facing copy changes are in scope. If Playwright needs new JavaScript/config files, obtain permission first.

Acceptance for each migrated path: both PHP versions lint; no unclassified PHP 8.4 exception/warning on the path; correct outputs and side effects; no unexplained UI or URL change; unchanged access controls; and rollback route tested. Cross-module smoke checks are required when shared `common/` PHP changes.

## 4. Environment and DevOps runway

### Local dual runtime using the existing Caddy design

1. Keep the existing local Compose stack: Caddy, `syc-fpm72`, `syc-fpm84`, MySQL 8.0, and the shared website volume. Use the development override and Compose watch to sync this checkout into the existing path. Validate the watch source/target and ensure both FPM containers see the same release bytes. Never use the volume-reset helper merely to test routing; it deletes content and DB volumes.
2. Route **only** the selected `rm_utils` paths to 8.4 under the existing `/pegasus_stx/rm_utils/...` URL, leaving other `/pegasus*` paths on 7.2. The Caddy matcher should cover both `/pegasus_stx/rm_utils` and `/pegasus_stx/rm_utils/*`, not `/pegasus_stx/rm_utils*` (which also matches similarly named paths). Keep path and query string unchanged. Test Caddy's adapted routing order for the named matcher and fallback; use an explicit route/handler order if the adapter does not produce the intended precedence.
3. Preserve the **same sensitive-file/log protection** in the 8.4 Pegasus handler before moving a URL. Do not copy the PHP 7.2 handler's temporary P2PShell Basic Auth into the patched PHP 8.4 route. Avoid sending all Pegasus PHP to 8.4 or changing Joomla/Boat Hire rules. Add a temporary diagnostic endpoint only in a protected local environment, verify runtime through a response/log, then remove it. The owner has authorised Caddy changes on the Docker repository's `php72to84` branch for this migration.
4. Check session continuity. Both runtimes currently use `files`, but an empty `session.save_path` may resolve to each container's private `/tmp`; their application users also differ. Set up a dedicated shared session store/path with matching serializer, cookie settings, permissions and lifetime, or keep users pinned to one runtime and force a clean re-login at boundary. Test a live session crossing 7.2→8.4→7.2 before promising seamless rollback. Do not assume shared website storage shares sessions.
5. Compare extension and ini/pool settings in the **running FPM containers**, not only Dockerfiles. The 8.4 image already has `mbstring`, `mysqli`, `pdo_mysql`, `curl`, `imagick`, etc.; `mcrypt` is absent. If a verified in-scope path needs it, decide between a maintained extension build and PHP-only replacement after consulting the owner of any `common/oss` dependency. Any Docker/ini/Compose edit requires permission before implementation.
6. Use `docker compose -f compose.yaml -f compose-dev.yaml config --quiet`, Caddy `validate`/`adapt`, route probes, PHP 7.2/8.4 lint, and acceptance journeys as gates. Keep staged copy and live VPS isolated, with no cross-environment DB, mail, API credentials, or writable file targets.

Caddy documents [handler sorting](https://caddyserver.com/docs/caddyfile/directives) and [path matcher semantics](https://caddyserver.com/docs/caddyfile/matchers); `/foo*` is broader than `/foo/*`. The [FastCGI root](https://caddyserver.com/docs/caddyfile/directives/php_fastcgi) must match the FPM-visible script path. PHP documents [file session storage](https://www.php.net/manual/en/session.configuration.php).

### Third-party audit

- Make a read-only inventory of every library reachable from in-scope routes: source path, version/license, loading mechanism, upstream support for PHP 8.4, required extensions, owner, and test path. Include copied `rm_utils/lib` WebCollect code and `common/oss` PHPMailer/phpseclib/WebCollect; inspect exact versions rather than assuming them from directory names.
- Search for `require`/`include`, `set_include_path`, manual autoloaders and runtime `class_exists()` probes. Where a Composer manifest exists in a bundled package, use it for metadata only; it does not imply the application was installed via Composer.
- On a **temporary copy**, use Composer `validate`, `show`, `outdated`, `audit`, and `why-not php 8.4` where a real lock file exists. Never run `composer update` across the application without a versioned dependency plan. Record incompatibilities and required changes in `common/oss` as owner actions, then retest after the owner supplies an updated library.
- Keep browser assets untouched to preserve UI. Assess their security separately only if necessary to the PHP runtime migration.
- Treat Bootstrap 3.4.1 and Bootstrap 5.3.2 as separate existing UI dependencies. Record which generation each canary route loads, route the corresponding static assets with it, and compare each page against its own version-specific baseline rather than expecting a common Bootstrap rendering.

### P2 landing-page compatibility evidence — completed 2026-11-10

The first Phase 2 batch is limited to `rm_utils/index.php` and its only PHP include, `common/lib/util_lib.php`, at checkout commit `8ccb44ff6328`. The landing page reads configuration and `data/util_apps.json`; it does not start a session, instantiate a database wrapper, or call MySQLi/PDO. No application PHP change is needed for this route. MySQLi report mode was therefore handled deliberately by deferring the compatibility shim or wrapper repair until the first DB-backed batch, where valid and deliberately invalid connections/queries can be tested. Applying a process-wide MySQLi change to this DB-free route would add risk without exercising it.

Temporary report-only tooling was installed under `/tmp/pegasus-phase2-tools` in `syc-fpm84`, outside the served tree: Rector 2.8.4 and PHPStan 2.3.5. Exact checkout copies, not the drifted deployed volume, were scanned.

- Rector's PHP-through-8.4 dry run reported suggestions in both files. They were cosmetic long-array-to-short-array rewrites, one PHP 7-compatible null-coalescing rewrite, and one PHP 8-only `str_contains()` rewrite. None fixes a demonstrated 7.2→8.4 defect; the PHP 8-only suggestion would break rollback compatibility. All suggestions were reviewed and rejected, and Rector did not edit the checkout.
- PHPStan level 2 with `phpVersion: 80400` reported nine findings, all in the unused `u_sendfile_sftp()` function because the excluded/manual phpseclib `Net/SFTP.php`, `Net_SFTP`, and its constants were not loaded into the isolated scan. `rm_utils/index.php` had no finding. The reviewed external baseline is `C:\Users\MarkE\AppData\Local\Temp\pegasus-phase2-phpstan-baseline.neon` (1,424 bytes; SHA-256 `5ec0627a1743b7a72a1be4ea9d2cc3d94d7c6dfdf6b2c6ee20b4a00030e2ad55`). No suppression or tooling file was added to the repository.
- Exact checkout copies of both PHP files passed syntax lint in the running PHP 7.2 and 8.4 containers. With the same copied `common.ini` and `util_apps.json`, both runtimes rendered exactly 7,560 bytes with SHA-256 `8f29c39b517615b661b67a421e31ed9ac95f4712d846b27e3fa4436da956dd6d`; no focused test was added because there is no behavior change to capture.

The owner confirmed on 2026-11-10 that the checkout `common/lib/util_lib.php` is authoritative. Its SHA-256 `0a5ac4a9e95baf4e9d4404124a15f182bdce3883400310366242501a2f312cdc` was synced to the shared local deployed volume, replacing the drifted copy that enabled the obsolete `u_sendmail()` implementation. Both FPM containers see the authoritative hash; the replaced copy remains recoverable from the external temporary evidence directory.

### P3.1 landing-page route gate — completed 2026-11-10

The active Caddy 2.11.7 configuration validates successfully. Its `@php84` matcher contains only the landing page's three URL forms, favicon, Bootstrap 5.3.2 assets, and Bootstrap Icons 1.11.3 assets. That handler retains the Pegasus INI/log block and sends PHP to `syc-fpm84:9000`. The remaining `/pegasus*` handler retains Basic Auth, the same INI/log block, and `syc-fpm72:9000`.

Read-only HTTP probes confirmed:

- `/pegasus_stx/rm_utils` returns 308 to the slash form; the slash form and explicit `index.php` return 200 with 6,104 bytes, without Basic Auth;
- the PHP 8.4 access log records both landing-page executions as `GET /pegasus_stx/rm_utils/index.php` with 200;
- the favicon, Bootstrap CSS/bundle, and Bootstrap Icons CSS all return 200;
- unselected `rm_utils/helloworld.php`, `rm_utils/common.ini`, and `rm_sailor/` return 401 from the authenticated legacy handler before reaching PHP; and
- the Caddy matcher/config inspection confirms unmatched Pegasus PHP remains directed to PHP 7.2. No routing, container, or source file was changed during this gate.

### P3.2 landing-page dual-runtime parity — completed 2026-11-10

After resolving the shared-file drift, the mounted `rm_utils/index.php` and authoritative `common/lib/util_lib.php` passed syntax lint in both running FPM containers. Executing the landing page against the same mounted configuration and `util_apps.json` produced exactly 6,104 bytes with SHA-256 `10848f634d123897e5c43e00bbfeabd55a8066ce90282d47d8158ec51e0ea519` on both PHP 7.2 and PHP 8.4. The routed PHP 8.4 HTTP response is also 200 with the same byte count and hash. The slash redirect remains 308 and required assets remain 200.

A new fixed 1440×1200 Edge screenshot is stored outside Git at `C:\Users\MarkE\AppData\Local\Temp\pegasus-php84-migration\php84-rm-utils-index-20261110.png`. It is 119,389 bytes with SHA-256 `33556f75fb219d7049e71d3fb7d61e6bfc97be4801a86f2e6f998a3fdbc60cbb`, exactly matching the recorded PHP 7.2 screenshot's size and hash. Visual inspection also confirms the same header, utility cards, styling, and absence of an authentication prompt.

The PHP 8.4 log records successful 200 requests with no warning, notice, deprecation, fatal error, or exception. This landing page does not start a session, query or modify the database, create a download, write a file, or call an external service, so session continuation and side-effect comparisons are not applicable to this route. Rollback remains the validated Caddy fallback to PHP 7.2 using the same dual-compatible source.

### P3.3 first read-only DB-backed batch — completed 2026-11-10

The first P3.3 batch is `rm_utils/handicap_numbers.php`, selected because it takes no request parameters and performs only database SELECTs. The owner authorised the local directory permission prerequisite. The shared Pegasus `logs`, `tmp`, and `data` trees now retain owner UID 82, use FPM group GID 33, directory mode `2775`, and file mode `0664`. Cross-container probes confirmed that the PHP 7.2 worker can create and the PHP 8.4 worker can write/remove a file in each tree; probe files were removed. This permission correction is local runtime state only and is not yet durable in `dev/02-dev-edits.sh`.

The reachable `common/classes/db_class.php` wrapper received a narrow dual-runtime compatibility repair:

- explicitly set `MYSQLI_REPORT_OFF` before connecting so PHP 8.1+ does not replace the wrapper's return/error contract with `mysqli_sql_exception`;
- suppress the native connection warning because the wrapper records and renders the same failure itself;
- check `connect_errno` before calling `set_charset()`, because PHP 8 treats the failed `mysqli` object as closed and otherwise throws `Error`;
- handle an absent caller backtrace frame without PHP 8 warnings; and
- pass explicit PHP 7.2-equivalent `ENT_COMPAT | ENT_HTML401` and UTF-8 arguments to `htmlentities()` in error/debug logging.

No public method signature or successful-query behavior changed. PHPCompatibility `7.2-8.4` reports no finding in the changed wrapper or route. Rector's final dry run proposed only short-array/null-coalescing style rewrites, which were rejected. PHPStan level 2 reports 15 reviewed pre-existing findings across the reachable scan: nine unloaded manual-phpseclib symbols, plus one potentially undefined variable in an unused boat-update method, two unused/invalid DB helper branches, two template diagnostics, and a top-level `__FUNCTION__` diagnostic. None is introduced by this batch or exercised by the route; they remain recorded for their relevant later batches.

Dual-runtime behavior evidence against the same mounted source and database:

- valid report: PHP 7.2 and 8.4 both return 26,475 HTML bytes, SHA-256 `897626132126949810df5656b5f99306e0b34304879424852bb64c6fbdd8ba45`, with empty stderr;
- invalid SELECT against a deliberately absent table: both preserve the valid page fingerprint, return `false`, and write the expected application error log; an apostrophe in the probe query remains unescaped on both versions;
- invalid connection: both exit through the same 2,632-byte controlled error page, SHA-256 `ffb5ce585938c8df3d0e264db17d6ec960e0def70cfd7643543d2ec1785604e3`, with empty stderr and no exception; and
- neither the valid report nor failure probes modify database data.

The authorised Caddy matcher now includes only `handicap_numbers.php`, its local stylesheet, and the Bootstrap 3.4.1/jQuery/Growl/FormValidation/clock assets it requests. The route and every asset return 200 through PHP 8.4; unselected `entry_check.php` remains on the authenticated fallback and returns 401. A temporary local Caddy reload routed the selected matcher to PHP 7.2 for rollback rehearsal, returned the same 26,475-byte report, and recorded the request in the PHP 7.2 access log. The matcher was then restored to PHP 8.4, revalidated, reloaded, and confirmed healthy.

Fixed 1440×1200 Edge captures are stored outside Git as `php72-handicap-numbers-20261110.png` and `php84-handicap-numbers-20261110.png` under `C:\Users\MarkE\AppData\Local\Temp\pegasus-php84-migration`. Both are 64,610 bytes with SHA-256 `b5e1e3e4c480b1b1d9bf3700eceae16d1ab54d207ac5ad165762765986648f95`; visual inspection confirms the expected styled handicap table. This route uses a fixed application session ID rather than user login state. Separate container-private sessions initialise successfully and render identically, so the rehearsed rollback does not require shared user-session continuity for this route.

## 5. Step-by-step phased roadmap

Check the box only after its stated evidence is recorded below. A less capable implementation agent should work one path at a time and stop at any unresolved dependency or owner action.

### Phase 0 — Baseline and protection

- [x] P0.1 Record the Git commit, currently untracked files, running image tags/digests, `php -v`, `php -m`, effective FPM ini/pool settings, Caddy adapted route, and Compose configuration. Store no credentials or backup contents in Git. Completed 2026-10-27; see “P0.1 baseline evidence”.
- [x] P0.2 Preserve the known-good 7.2 and 8.4 image tags/digests and deployable Caddy config; confirm the VPS has a working rollback path. Do not rebuild the EOL 7.2 base as a prerequisite. Completed 2026-10-27 under the owner-approved registry rollback and public-only production access policy; migration-image rollback rehearsal remains a later release gate.
- [x] P0.3 List all public/CLI/cron entrypoints reachable from `rm_utils`, the `common/` PHP they load, file outputs, external services, and DB tables they touch. Exclude `rm_admin/`, `testing/`, `common/oss/` from changes. Completed 2026-10-27; see “P0.3 `rm_utils` entrypoint and dependency inventory”.
- [ ] P0.4 Choose a disposable, anonymised MySQL 8.0 data snapshot and safe mail/API targets. Capture representative 7.2 responses, exports, logs and screenshots before touching application PHP. In progress: an owner-approved anonymised logical snapshot, safe-target policy, landing-page evidence, and a successful read-only DB-backed report/application-log comparison are recorded above. A disposable restore and export baseline remain outstanding.

### Phase 1 — Audit and route selection

- [x] P1.1 Produce PHP 8.4 lint and PHPCompatibility findings for `rm_utils` plus reachable shared PHP; triage parse/fatal issues first, then changed behaviour, then deprecations. Record file and line, whether actually reachable, and planned test. Completed 2026-10-27: all 87 checkout files pass PHP 7.2/8.4 lint; PHPCompatibility returned 4 errors and 28 warnings with grouped triage/tests above; manual inspection adds the PHP 8.1+ MySQLi exception-mode risk. Deployed-volume drift is separately recorded.
- [x] P1.2 Build the third-party and extension matrix. Send every required `common/oss` change to its owner with a minimal reproducer; do not edit that tree. Completed 2026-11-02: the matrix, dual-runtime lint/load evidence, missing-file reproducers, and route-specific owner actions are recorded above; `common/oss` was not edited.
- [x] P1.3 Select the **first low-side-effect `rm_utils` route** (landing page or read-only report), record its exact URL and dependencies, and decide the minimum PHP edits. Keep cron/import/export/publish actions for later batches. Completed 2026-11-02 for the landing page; no application PHP edit is required.
- [x] P1.4 Request permission for the exact non-PHP infrastructure/tooling/test files needed for the selected implementation, with proposed diff and validation evidence when available. Do not alter those files before permission. Completed 2026-11-02: the owner authorised the `php72to84` Docker branch, Caddyfile changes, image rebuild, and `syc-webserver` recreation; local evidence is recorded above.

### Phase 2 — Narrow PHP compatibility work

- [x] P2.1 Apply only 7.2-safe PHP changes to the selected route and reachable shared PHP. Address MySQLi report mode deliberately; preserve public APIs and output. Completed 2026-11-10: the selected route is DB-free and requires no PHP edit; MySQLi handling is explicitly deferred to the first DB-backed batch.
- [x] P2.2 Run Rector dry run with selected rules, review each proposed edit, apply only relevant fixes, then lint on both runtimes. Avoid broad typing/style rewrites and all excluded directories. Completed 2026-11-10: all suggestions were cosmetic or rollback-incompatible and were rejected; exact checkout files lint on both runtimes.
- [x] P2.3 Run PHPStan/Psalm as chosen, update a reviewed baseline, and fix newly introduced high-confidence errors. Record any deferred warnings with reason and owner. Completed 2026-11-10: no landing-page finding; nine known manual-phpseclib discovery findings are recorded in an external reviewed baseline.
- [x] P2.4 Add a focused integration or unit test only where it captures a changed PHP behaviour; otherwise record manual/scripted baseline evidence. Completed 2026-11-10: no behavior changed, so no test was added; exact-source CLI rendering is byte-identical on PHP 7.2 and 8.4.

### Phase 3 — Dual-run QA, `rm_utils` first

- [x] P3.1 After authorised Caddy/Compose changes, validate the route matcher, same URL, correct FPM, expected P2PShell/access-control state, blocked files, static assets and fallback paths. Verify all other Pegasus paths remain on 7.2. Completed 2026-11-10: Caddy validates; selected URLs/assets pass; FPM 8.4 logging and authenticated PHP 7.2 fallback behavior are confirmed.
- [x] P3.2 Test the selected route on the same data with 7.2 and 8.4; compare HTML/DOM/screenshots, status, redirects, downloads, DB effects and logs. Confirm session continuation or documented re-login behaviour. Completed 2026-11-10: HTML and screenshot hashes are identical across the recorded 7.2 baseline and current 8.4 canary; the route is session-free and has no side effects.
- [ ] P3.3 Repeat P2–P3 in small `rm_utils` batches: read-only views; calculations/reports; programme/results; CSV import/export; WebCollect/Brevo integrations; scheduled/CLI jobs. Keep each batch independently reversible. In progress 2026-11-10: the first read-only DB-backed `handicap_numbers.php` batch passes dual-runtime output, failure-path, browser, asset, and rollback checks.
- [ ] P3.4 After shared `common/` PHP changes, smoke-test other in-scope modules under 7.2; do not include excluded modules as migration targets.

### Phase 4 — Canary and production cutover

- [ ] P4.1 Release the tested PHP code and required 8.4 image/config together; check digests, configuration, permissions and external credentials on staging. Run acceptance matrix end to end.
- [ ] P4.2 Move one low-side-effect `rm_utils` route to patched PHP 8.4 in production. Monitor 5xx, PHP exceptions/warnings, latency, sessions, expected access controls, generated outputs, mail/API effects and user reports against the 7.2 baseline. Expand route-by-route only after the preceding route passes.
- [ ] P4.3 For rollback, change the **Caddy route** for affected URLs back to `syc-fpm72`, reload/validate Caddy, and verify the old runtime plus sessions. This works only while changed PHP remains 7.2-compatible, the old image exists, and data/files remain mutually readable. If sessions cannot be shared, use planned re-login rather than claiming instant seamless rollback.
- [ ] P4.4 Do not use database restoration as the normal rollback: production activity may have occurred. If a tested write path has bad side effects, halt it and use a separately rehearsed, owner-approved recovery procedure for specific rows/files.

### Phase 5 — Finish and simplify

- [ ] P5.1 After all in-scope routes pass, remove temporary compatibility shims only if tests still pass; keep full error reporting in staging and a monitored production policy.
- [ ] P5.2 Audit remaining Pegasus 7.2 routes and owner actions before retiring the 7.2 service. Do not claim whole-application migration while excluded or blocked routes still depend on it.
- [ ] P5.3 Update this document with final route matrix, versions, test links, exceptions, rollback rehearsal, approvals, and completion date; commit it alongside approved code/config changes.

## Decision and progress log

| Date | ID | Decision / progress | Evidence or next action |
| --- | --- | --- | --- |
| 2026-10-27 | D-001 | Plan for a direct 7.2→8.4 runtime transition, beginning with `rm_utils`, with dual-compatible PHP for rollback. | Conditional on Phase 1/3 results. |
| 2026-10-27 | D-002 | Use current Caddy + dual FPM topology; the existing 8.4 Pegasus handler must gain auth parity before use. | Current Caddyfile and running containers. |
| 2026-10-27 | D-003 | No application, Docker, Caddy, database, or `common/oss` changes made during planning. | Initial document only. |
| 2026-10-27 | D-004 | P0.1 baseline captured and sanitised. No runtime, application, infrastructure, database, or `common/oss` state was changed. | Continue with P0.2. Preserve exact image digests and active modified Caddyfile; resolve sessions and write permissions before dual-run write testing. |
| 2026-10-27 | D-005 | P0.2 registry audit confirms both PHP images are recoverable by immutable digest. Production Caddy configuration is image-baked; the local running Caddy image is not available from the registry by its exact digest and its tag now points elsewhere. | Use a new versioned Caddy image for the reviewed migration route. Complete a read-only VPS preflight and staging rollback rehearsal before production canary. |
| 2026-10-27 | D-006 | Owner accepts the Caddy image currently in the private registry as the rollback target and will not provide private VPS access. | P0.2 completes using registry evidence, public HTTP checks, and an operator runbook. Future production verification is limited to public URLs. |
| 2026-10-27 | D-007 | P0.3 static inventory completed: 28 root scripts, 25 PHP components, five external-system boundaries, and broad DB/file side effects. | Begin P0.4 with an isolated data/safe-endpoint decision and PHP 7.2 baselines; `index.php` is the lowest-side-effect first candidate. |
| 2026-10-27 | D-008 | Owner confirms that Pegasus email addresses and telephone numbers are anonymised and approves the current data as the source for migration-test snapshots. | Logical snapshot captured outside Git and integrity-checked. Restore it to a separately named disposable schema before any write test; do not alter the source schema. |
| 2026-10-27 | D-009 | P0.4 permits smtp4dev only after effective SMTP routing is verified; all direct Brevo, WebCollect, Dutyman, SFTP and callback paths remain blocked until their test targets are proven safe. | PHP 7.2 `rm_utils/index.php` CLI, HTTP and screenshot evidence captured; DB-backed, export and successful application-log baselines remain open. |
| 2026-10-27 | D-010 | Owner removed Basic Auth from the local PHP 7.2 Caddy route. A successful HTTP response and visual screenshot baseline were captured without modifying the route further. | Keep production authentication requirements unchanged. DB-backed probes exposed a non-resolving configured DB host and unwritable Pegasus logs; obtain permission for exact non-PHP environment corrections before continuing those baselines. |
| 2026-10-27 | D-011 | Effective `rm_utils` evidence identifies `localhost` in both deployed `common.ini` and the probe sessions, despite Docker `.env` specifying `syc-mysqldb80`. The log target is inside `syc-websites-volume`; UID/GID `82:82` directories are not writable by FPM workers running as `33:33`. | Compare an exact owner-confirmed working database URL and its configuration/session path before proposing a correction. |
| 2026-10-27 | D-012 | Owner changed `common.ini` to `syc-mysqldb80`; the deployed value matches, container DNS resolves it, and a session-free PHP 7.2 `SELECT 1` succeeds against MySQL 8.0.44. | Earlier fixed-ID probe sessions still cache `localhost`. Obtain permission to remove only those test-created sessions, then repeat the read-only HTTP baselines. |
| 2026-10-27 | D-013 | Owner authorised removal of four test-created sessions. Repeated read-only HTTP baselines now connect successfully and return distinct expected forms/tables without MySQL, fatal or parse diagnostics; regenerated sessions use `syc-mysqldb80`. | Recommend targeted `82:33` ownership plus `2775` directories/`0664` files for local `logs`, `tmp`, and `data`, made durable after the existing development `chown`. Await permission before changing the shell script or volume metadata. |
| 2026-10-27 | D-014 | P1.1 syntax gate passes for all 87 checkout PHP files on PHP 7.2 and 8.4. The deployed volume has five extra files, one missing checkout file, and eight same-path byte differences, so deployed responses are not accepted as source equivalence evidence. | Install PHPCompatibility 10/PHPCS 4 in an isolated temporary directory only after permission, then run `testVersion=7.2-8.4`. Reconcile source/deployed drift separately before dual-runtime response comparison. |
| 2026-10-27 | D-015 | P1.1 completed using owner-approved temporary PHPCompatibility 10 alpha2/PHPCS 4 tooling: 4 errors and 28 warnings triaged into changed HTML escaping, CSV defaults, line-ending configuration, `E_STRICT`, and pre-7.2 diagnostic semantics. Manual audit keeps reachable MySQLi exception mode as the highest-priority behavioural risk. | No automatic fixes applied. Carry each group into its relevant small route batch and planned parity test; proceed to P1.2 third-party/extension matrix. |
| 2026-11-02 | D-016 | The PHP 7.2 `memory_limit=-1`, `post_max_size=8M`, and `upload_max_filesize=6M` settings will not be copied to PHP 8.4; its current `128M`, `32M`, and `32M` values are intentional. Basic Auth on PHP 7.2 was a temporary P2PShell mitigation and is not required on patched PHP 8.4. | Test PHP 8.4 workflows against its effective limits. Preserve sensitive-file/log blocks without adding the obsolete Basic Auth control to migrated routes. |
| 2026-11-02 | D-017 | Migration infrastructure changes are authorised on `origin/php72to84` in `syc-server-docker`; the confirmed live Compose directory is `/home/sycuser/docker-compose`. | Increment the Caddy image version for each canary route image, update live `.env`, and recreate only `syc-webserver` after uploading dual-compatible PHP files and verifying them on 7.2. |
| 2026-11-02 | D-018 | The first local canary is the `rm_utils` landing page. The Docker checkout was already aligned with `origin/php72to84`; Caddy routes the landing page and required assets to PHP 8.4, while unselected PHP retains the PHP 7.2 fallback. Local image `syc-caddy:2.11.4-11` validates and the recreated webserver is healthy. | Do not push or deploy this image until the owner is ready for the live operator procedure. Capture the pushed immutable digest before editing live `.env`. |
| 2026-11-02 | D-019 | The obsolete `PHP72TO84v1.md` is absent from the Docker repository's `php72to84` branch. | Treat this repository's `PHP72TO84.md` as the authoritative migration plan. |
| 2026-11-02 | D-020 | The `rm_utils` UI is mixed-version: the landing page uses Bootstrap 5.3.2, while many template-driven utilities use bundled Bootstrap 3.4.1. | Keep both asset trees unchanged. Add `bootstrap341` paths only when a corresponding utility enters a PHP 8.4 route batch, and test that route against its Bootstrap 3 baseline. |
| 2026-11-02 | D-021 | P1.2 dependency/extension audit completed. The running PHP 8.4 image has the required extensions and the reachable phpseclib/WebCollect code lints and loads, but `common/oss` is Git-ignored; phpseclib and WebCollect lack reliable provenance, and PHPMailer is absent. The owner subsequently confirmed that the stale Bootstrap Dialog references were not required, and they were removed. | Continue the landing-page canary. Hold each affected later route until its recorded owner action and controlled integration/UI test are complete. |
| 2026-11-10 | D-022 | Phase 2 for the landing page is complete without an application PHP edit. Rector changes were rejected after review, PHPStan has no landing-page finding, and exact checkout sources render byte-identically on PHP 7.2 and 8.4. | The recorded deployed-file drift was resolved by D-024. |
| 2026-11-10 | D-023 | P3.1 completed: the active Caddy route validates, serves only the selected landing-page URLs/assets through PHP 8.4, and preserves the authenticated PHP 7.2 fallback plus INI/log blocks. | The recorded P3.2 gate was resolved by D-024. |
| 2026-11-10 | D-024 | The owner confirmed the checkout `util_lib.php` is authoritative; it was synced to the shared local volume. P3.2 then passed with identical PHP 7.2/8.4 HTML and identical PHP 7.2/8.4 screenshot fingerprints. | The landing-page batch is locally accepted. Continue P3.3 with one read-only DB-backed utility after selecting its exact inputs and confirming the disposable-data/logging prerequisites. |
| 2026-11-10 | D-025 | The local Pegasus writable trees were changed to owner/group `82:33`, directory mode `2775`, and file mode `0664`. The first DB-backed batch repairs the shared MySQLi wrapper and moves `handicap_numbers.php` plus only its required assets into the local PHP 8.4 matcher. Dual-runtime success/failure output, screenshots, and rollback all match. | Keep the permission change local until separately authorised for `dev/02-dev-edits.sh`. Continue P3.3 one reversible route category at a time; do not begin DB-write or external-service batches before their disposable targets are ready. |

## Questions to resolve before implementation

1. Does “no changes to the database” mean no schema/configuration changes (while existing utilities may write to an isolated test DB and continue normal production writes), or must migration testing avoid all DB writes even in a disposable clone?
2. Which `rm_utils` workflows are most business-critical, and which exact URL should be the first canary? Are its cron jobs invoked by URLs, `docker exec`, or an external scheduler?
3. May users be asked to start a new application session when a route first moves to 8.4 or rolls back, or is seamless cross-version session continuity required?
4. Who owns `common/oss/`, and can that owner provide updated packages if a PHP 8.4 incompatibility is proven? Are there third-party services or credentials that must be stubbed in staging?
