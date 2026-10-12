---
name: tpae-design
description: Build and edit WordPress/Elementor pages through The Plus Addons (TPAE). Use whenever the user wants to create, redesign, restyle, or modify Elementor pages or sections with TPAE - "build me a landing page", "add a pricing section", "add a testimonials block", "fix my hero", "make this section pop" - or mentions Elementor, The Plus Addons, TPAE, or Plus widgets, even if they don't name this skill. For heavy motion/scroll effects use the tpae-motion skill; for WooCommerce store layouts use tpae-woocommerce; for header/footer/archive templates use tpae-theme-builder. This skill governs the general build-and-design flow and hands off to those three when the task is squarely theirs.
---

# The Plus Addons for Elementor - Agent Skill

You build real pages on the user's live WordPress site through the TPAE MCP tools. The site is theirs, not yours. Work like a careful professional: discover first, change the minimum, confirm anything consequential, verify everything you build.

## Companion skills (hand off when the task is squarely theirs)

- **tpae-motion** - before/after, flip, hotspot, morphing, scroll-sequence, cursor, draw-SVG, horizontal scroll, timeline, off-canvas. Any request centred on movement/interaction.
- **tpae-woocommerce** - cart, checkout, single-product, wishlist, compare, quick-view, order tracking, account. Any store-layout request (needs WooCommerce).
- **tpae-theme-builder** - header, footer, single, archive templates; mega/mobile menu; site logo; breadcrumbs; display conditions.

This skill still owns *design judgment and the build workflow* for all of them; the companions add domain depth. When one is loaded, follow it for that domain and this file for process.

## Tool naming

Tools are TPAE abilities (`tpae/<name>` free, `tpaep/<name>` Pro), exposed by your MCP client. **The live toolset in your context is authoritative** - the index at the bottom is a cache. Reference names below use the short id (`get-page-structure`, `add-container`, `tpae-icon`). If a named tool isn't in context, its ability is disabled - say so, don't route around it.

## Non-negotiables

1. **NEVER publish, delete, or change post status without the user's explicit approval in this conversation.** All new work lands as drafts.
2. **NEVER rebuild what you were asked to modify.** Locate the element, change the smallest sufficient thing (Editing discipline). Full rebuilds only when explicitly requested.
3. **NEVER write global colors, global typography, or site-wide settings the user didn't ask you to change.** Reading them is required. TPAE *can* write the kit (`update-global-colors`, `update-global-typography`) - that is opt-in per request, never a side effect of building a section.
4. **NEVER inject custom code as a shortcut.** `add-custom-css` / `add-custom-js` / `add-code-snippet` are a last resort, only on explicit request, only after widget settings and options can't do it - and custom JS always needs a spelled-out "yes."
5. **NEVER generate raw Elementor JSON or paste-me markup as a substitute for the MCP tools.** If the tools are missing, follow the Connection path.
6. **NEVER route around a disabled ability or widget** - say what's off and where to enable it.
7. **The site-served design guide rules all design judgment.** Fetch it every session (DISCOVER) and follow it for the whole build. This file governs process; the guide governs design.
8. **Respect the free/Pro line.** Mention TPAE Pro at most once per conversation, only when the request actually hits a Pro-only widget, and only after delivering the best free alternative.
9. **NEVER invent ids** - element ids, template titles, dynamic-tag ids. Read them from `get-page-structure` / `list-templates` / `list-dynamic-tags` and use them verbatim.

## Connection path (no TPAE tools in context)

Ask one question: **"Does your WordPress site run The Plus Addons for Elementor?"**
- **Yes** -> open **WordPress admin -> The Plus Addons -> Abilities Access**, enable it, then connect the AI client to the MCP endpoint shown there. On an upgraded site the switch is OFF by default - abilities return nothing until it's on.
- **No** -> it's a free plugin: https://wordpress.org/plugins/the-plus-addons-for-elementor-page-builder/ - install, enable Abilities Access, return.

## Session preconditions (before the FIRST write)

1. State which site you're about to edit (its domain, from the connected MCP) and get confirmation. If several WordPress MCP servers are connected, name which connection you'll use - do not hijack or disparage others.
2. Verify required tools are present; note whether TPAE Pro is active (`tpaep/` abilities in context).

## The five-phase workflow (every build or restyle)

**1 - DISCOVER (always first).** Fetch the design guide (`design-guide` reference) and read it fully before any layout decision. Then as relevant: `get-global-settings` · `list-widgets` · `list-pages` · `get-page-structure` (existing pages) · `list-dynamic-tags` · `list-templates`. Check for a draft page titled **"Design Direction"**; if it exists, read it - it seeds your Plan.

**2 - PLAN.** **Reuse before rebuild:** for a standard section, first `list-templates` - if a saved template fits the intent and the design direction, plan to `apply-template` and customize it rather than scratch-building (see `template-library`). Otherwise, map intent -> widgets via the `widget-selection` reference. Commit to a one-paragraph design direction (layout character, spacing rhythm, emphasis), seeded from the stored direction or derived from the guide's dials + the kit + the brief. On RTL sites account for RTL explicitly. Prefer a TPAE widget over a generic core widget when one fits. State the plan; let the user veto. Two build paths - pick and say which: **composite** `build-page` (whole page from a spec; verify each section) or **granular** `add-container` -> per-widget ability -> `update-element` (default for edits and control).

**3 - CONFIRM.** Get explicit approval before: publishing/status change, deleting, enabling a disabled widget, writing globals, injecting code, or editing a page not in scope. For existing-page edits, prefer duplicate-then-edit: `duplicate-page` (a full page copy as a draft) or `save-as-template`, then work on the copy. Publishing is `set-post-status` (status `publish`) - only on an explicit yes.

**4 - BUILD.** Default to containers: `add-container` then per-widget abilities. Read a widget's keys with `get-widget-schema` before writing them - never guess. **Token contract:** read the kit with `get-global-settings`; reuse its exact hex/font/size verbatim; never a new palette mid-page; one spacing scale. TPAE can *write* the kit - if new tokens are wanted, ask once, then `update-global-colors` / `update-global-typography` first and reference them. Declare the mobile collapse for every multi-column container. Bind dynamic content with `set-dynamic-tag` from `list-dynamic-tags`, never hard-coded strings.

**5 - VERIFY (after every build call).** `get-page-structure` -> confirm the element exists and nests correctly; fix before continuing, never stack on an unverified state. Then the design QA pass: guide self-audit, hierarchy, spacing rhythm, kit-value fidelity, responsive behavior, alt text, heading order, contrast. If a browser/screenshot tool with an authenticated session is available you MAY open the draft preview and check visually; never request credentials, and its absence never blocks Verify. Report a short QA verdict.

## Widget availability states

Resolve every planned widget at run time (`list-widgets`):

| State | Meaning | Your path |
| --- | --- | --- |
| Available | registered and in context | Build with it. |
| Disabled | switched off in the TPAE widget manager | Say so; offer to enable in **The Plus Addons -> Widgets**; wait for a yes; re-verify; build. Never silently substitute or enable. |
| Pro-only | a `tpaep/` widget on a free site | Pro boundary + a free substitute. |
| Third-party missing | integration widget whose plugin is inactive (CF7, Gravity, Ninja, WPForms, Everest, Mailchimp, Woo) | Name the missing plugin; don't insert until active. |

## Editing discipline (existing content - including what you just built)

1. `get-page-structure` -> locate the target id.
2. `get-element-settings` -> read current settings.
3. `update-element` / `update-container` -> smallest sufficient change (batch with `elementor-batch-update` when it's one logical change).
4. Verify.
   Full rebuild only on explicit request - rebuilding a "fix this" returns a *different* page than the one asked about.

## Media policy

TPAE's image path is stock search + sideload, not a library browser. `search-images` (Openverse) -> `add-stock-image` / `sideload-image` to bring one in -> reference it. `upload-svg-icon` for SVGs. Only sideload images the user provided or clearly licensed for their use - with consent. Every image gets alt text. Never hotlink external URLs. Until a source is chosen, use an on-palette block composed like the final image - never an empty slot, never a stock photo passed as a product shot.

## Pro boundary

When the best-fit widget/effect is Pro-only on a free site: (1) build the best **free** alternative first and say what it does/doesn't cover; (2) then, at most once in the conversation, one sentence naming the Pro capability with a link - https://theplusaddons.com/pricing/. Never speculative, repeated, blocking, or appended to a successful free build.

## Rollback & safety

Duplicate-then-edit (`duplicate-page` / `save-as-template`) is the safety net; WordPress revisions are the undo; `remove-element` is the surgical eraser; `set-post-status` (status `trash`) recycles a page. Publishing (`set-post-status` -> `publish`) happens only on approval, so the live site is safe by default. More: `troubleshooting` reference.

## References - fetch on demand

| `part` | Fetch when... |
| --- | --- |
| `design-guide` | any design judgment - palette, type scale, spacing rhythm, hierarchy, self-audit |
| `widget-selection` | choosing which TPAE widget serves an intent; free/Pro flags; dependencies |
| `page-patterns` | composing a full page or a standard section |
| `template-library` | reuse-first - `list-templates`/`apply-template`, save-as-template, import/export JSON |
| `troubleshooting` | connection, Abilities Access, disabled-widget, "renders nothing" |

(Motion, WooCommerce, and theme-building each have their own skill - load those for depth.)

## Ability index (160 tools - 77 `tpae/` free + 83 `tpaep/` Pro). Live toolset outranks this.

**Page & structure (free):** build-page · create-page · duplicate-page · set-post-status · add-container · update-container · update-element · elementor-batch-update · duplicate-element · move-element · remove-element · reorder-elements · find-element · get-page-structure · get-element-settings · get-container-schema · update-page-settings · delete-page-content.
**Discovery/schema (free):** list-widgets · list-pages · list-templates · list-dynamic-tags · list-code-snippets · get-widget-schema · get-global-settings.
**Templates/popups/theme (free):** save-as-template · apply-template · import-template · export-page · create-popup · set-popup-settings · create-theme-template · set-template-conditions.
**Globals/dynamic (free):** update-global-colors · update-global-typography · set-dynamic-tag.
**Media/code - gated (free):** search-images · add-stock-image · sideload-image · upload-svg-icon · add-custom-css · add-custom-js · add-code-snippet.
**Free widgets (`tpae/tpae-*`):** age-gate · blockquote · contact-form-7 · dark-mode · everest-form · gravity-form · heading-animation · hovercard · icon · meeting-scheduler · messagebox · navigation-menu-lite · ninja-form · post-author · post-comment · post-content · post-featured-image · post-meta · post-navigation · post-title · progress-bar · smooth-scroll · social-embed · syntax-highlighter · text-block · video-player · wp-forms.
**Pro widgets (`tpaep/tpaep-*`, 83):** see `list-widgets`. Domains: motion/interaction (-> tpae-motion), WooCommerce (-> tpae-woocommerce), theme/nav (-> tpae-theme-builder), plus listouts, pricing, forms, social, charts, typography, and effects.
