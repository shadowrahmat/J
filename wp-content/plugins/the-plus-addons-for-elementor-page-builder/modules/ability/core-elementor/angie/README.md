# TPAE <-> Angie bridge

In-editor Angie integration for The Plus Addons, modelled on Elementor's official
ACF reference module (`angie/modules/acf-rest-api/`) and the UAE/HFE implementations.
Gives Elementor's built-in Angie AI assistant a curated set of TPAE tools.

## How it works

1. **PHP** (`class-tp-angie-bridge.php`) exposes a curated FREE ability list at
   `POST /tpae/v1/angie/abilities/` and executes any of them via the wildcard
   `POST /tpae/v1/angie/{id}/`. It reuses each ability's existing `execute()` + schema.
2. **JS** (`tpae-angie-mcp-server.js` -> built `tpae-angie-mcp-server.min.js`) fetches
   that list, registers each as a tool on an `McpServer`, and hands it to Angie via
   `AngieMcpSdk.registerLocalServer()`. Tool calls route back to the REST bridge.
3. **Reclassification** (in `class-tp-ability-main.php`): when the built bundle is
   present, free `tpae/` abilities are marked `mcp.type='bridge'` so Angie's *generic*
   discovery skips them and this bridge is their single in-editor source. Pro `tpaep/`
   widgets stay on generic discovery (no curation needed) - so nothing double-lists.

## Master switch

`Tp_Angie_Bridge::is_built()` (presence of `tpae-angie-mcp-server.min.js`) gates
everything. Bundle present -> reclassify + enqueue. Bundle absent -> completely dormant
(abilities stay `type=tool`, nothing enqueued). This makes the whole feature atomic:
it can never half-activate and leave TPAE invisible to Angie.

## Verified (2026-09-04, Angie 1.1.16 on tpae-release)

- Bundle builds cleanly (esbuild, 816 KB ESM).
- Free `tpae/`: 77 registered, **0** visible to Angie generic discovery (reclassified).
- Pro `tpaep/`: 83, still on generic discovery.
- Bridge exposes **65** curated free tools (77 − 12 hidden).
- Angie sees **65 (bridge) + 83 (generic) = 148, no duplicates**.
- REST routes register; execute round-trips; unknown tool -> 404; input validation flows
  through the underlying ability. Lint clean; 160 abilities still register.

## Curation

`$angie_hidden` in the bridge hides 12 free abilities from Angie: the 10 redundant
`elementor-*` mirror verbs + `build-page` + `delete-page-content`. Tune from the
in-editor test log.

## Rebuilding the JS bundle

Source of truth is `tpae-angie-mcp-server.js`. It imports `zod/v3` (NOT `zod`) on purpose:
`@modelcontextprotocol/sdk` consumes zod v3 internally, so v3 both matches what the SDK
expects at runtime and avoids bundling a second (v4) copy of zod. To rebuild:

```
npm i -D esbuild && npm i @elementor/angie-sdk zod @modelcontextprotocol/sdk
esbuild tpae-angie-mcp-server.js --bundle --format=esm --platform=browser --minify \
  --define:process.env.NODE_ENV='"production"' --drop:console --drop:debugger \
  --legal-comments=none --outfile=tpae-angie-mcp-server.min.js
```

Current bundle: **503 KB raw / ~136 KB gzipped** (transfer). The zod/v3 alignment already
removed a duplicate ~330 KB of zod v4. Remaining weight is intrinsic to the deps
(@elementor/angie-sdk ~129 KB, MCP SDK + ajv, zod v3). Wire this into TPAE's release build.

## Client config

The bridge localizes its own `window.tpaeAngie = { root, nonce }` via a data-only classic
script (printed synchronously in the footer, before the deferred module), so it does NOT
depend on core's `wpApiSettings` being present in the editor. The JS reads `window.tpaeAngie`
first and falls back to `wpApiSettings` only if present. Routes are slash-free on both sides
(WP strips a trailing slash from the registered key, so a trailing-slash request 404s).

## Residual - the only unverified step

A human in-editor pass with a real Elementor/Angie account: activate Angie, turn on
TPAE Abilities Access, open the editor, and confirm Angie lists + drives the TPAE tools.
Every server-side, REST-routing, localization, and build layer is proven (incl. live
`rest_do_request` checks: list 200/65 tools, execute ok, bad-input surfaces the ability
error, unknown 404, logged-out 401); only the live browser handshake needs eyes.
