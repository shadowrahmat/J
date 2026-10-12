# Troubleshooting

Diagnose in this order: connection -> Abilities Access -> widget availability -> content/rendering -> rollback.

## Connection / no TPAE tools in context

- **No TPAE tools at all** -> either The Plus Addons isn't installed, or **Abilities Access is OFF**. On a freshly-upgraded site the switch defaults off (fresh installs default on). Point the user to **WordPress admin -> The Plus Addons -> Abilities Access**, enable it, then reconnect the AI client to the MCP endpoint shown there.
- **CDN / firewall / security plugin filtering AI clients** - Cloudflare, WAFs, and security plugins can block MCP/REST clients while the site works fine in a browser. Symptom: timeouts or HTML error pages instead of MCP/JSON responses. Fix: allowlist the AI client / the REST path in the CDN or security plugin.
- **In-editor Angie sees nothing** -> besides Abilities Access being on, the Angie bridge only feeds Angie when the Angie plugin is active. If Angie is active and TPAE tools still don't appear, confirm Abilities Access, then reload the editor.
- **Worked yesterday, dead today** -> most often an expired token or a revoked client. Reconnect.

## Auth / permission

- Tool calls succeed but writes fail -> the connected WordPress user lacks capability (needs `edit_posts`, or `manage_options` for global-kit and dashboard-setting writes). Say which operation failed and that the login used to connect needs a role with those rights.

## Widget availability

- **A tool this skill names isn't in context** -> its ability is toggled off (Abilities Access, or that specific ability disabled). Point to **The Plus Addons -> Abilities Access**; stop that path - never route around a deliberately disabled ability (SKILL.md).
- **A widget won't build / isn't offered** -> check `list-widgets`: disabled in **The Plus Addons -> Widgets** manager -> offer to enable (confirmed), then re-verify with `list-widgets`; Pro-only on a free site -> Pro boundary + free substitute; integration widget whose plugin is inactive (CF7, Gravity, Ninja, WPForms, Everest, Mailchimp, WooCommerce) -> name the missing plugin, don't build until it's active.
- **Elementor missing/inactive** -> the abilities have nothing to build on; the user must activate Elementor.

## "It renders nothing" / looks wrong

- **Element built but invisible** - check responsive visibility settings and whether it landed inside an unexpected parent (`get-page-structure`). Confirm the container's `content_width` and flex settings aren't collapsing it.
- **Stale styling after big changes** - TPAE inlines and caches per-post CSS/JS; a stale bundle shows old styling. Regenerate the cache (the plugin's cache-clear, or re-save the page) and hard-refresh. Editor vs frontend use different bundles, so verify on the front end.
- **Dynamic content shows nothing / a raw tag** - the dynamic tag isn't bound or its source is empty; re-bind with `set-dynamic-tag` from a valid `list-dynamic-tags` entry, and confirm the template's preview source.
- **A form widget shows nothing** - its form plugin is inactive or no form is selected; check the dependency and the selected form id.
- **A Pro widget shows a blank spot on a free site** - it's Pro-locked; follow the Pro boundary and substitute a free widget.

## Am I even using a TPAE widget? (identity + availability)

- **TPAE widgets are `tp-*`** (`tp-pricing-table`, `tp-icon`, `tp-info-box`...). A `premium-addon-*` widget is **Premium Addons for Elementor - a different plugin**, not TPAE. On sites where both are active, a naive `list-widgets` search (e.g. "pricing") can surface the other plugin's widget. Before building, confirm the widget id starts with `tp-`; never present a `premium-addon-*` widget as a TPAE one.
- **TPAE widgets are opt-in and some are OFF.** If a widget you expect (e.g. `tp-pricing-table`) is absent from `list-widgets` / `get-widget-schema`, it's disabled in the widget manager (**The Plus Addons -> Widgets**), not missing. Say so and offer to enable it; don't silently fall back to another plugin's widget. It registers on the next request after enabling.

## A color or style I set isn't applying

- **Many TPAE style controls use a gradient/global-preset system** (keys like `*_tp_gg_selector_store`, `button_global_style_preset`). Setting a plain `*_color` in the raw settings often does **nothing** - the rendered color comes from the preset/gradient store, and the theme kit's global button/heading styles can override per-widget values.
- **Reliable ways to actually change the color:** (1) set it in the widget's own control in the editor; (2) if driving by prompt, prefer the widget's dedicated ability (e.g. `tpae-pricing-table`) which maps clean params to the right keys; (3) as a last resort, Elementor **custom CSS scoped with the `selector` keyword + `!important`** (`selector .elementor-button{...}`) - an unscoped rule leaks globally and a low-specificity one loses to the kit's global styles; (4) for a small self-contained element (a badge/pill), an inline-styled snippet in a text widget is fully controllable.
- **Symptom that means a global override won:** text or background renders white/black or the theme accent instead of your value (e.g. white text on a white button = "the button vanished"). Verify by reading the element's *computed* style, not the stored setting.

## Rollback & safety

Duplicate-then-edit (`save-as-template` before risky work) is the safety net. WordPress revisions restore content history (wp-admin -> the page -> Revisions). `remove-element` surgically removes one mistake by id. Nothing publishes without approval (SKILL.md non-negotiable 1), so the live site is safe by default.
