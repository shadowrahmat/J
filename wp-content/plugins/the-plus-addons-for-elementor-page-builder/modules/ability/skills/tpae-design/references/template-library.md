# Template library - reuse before you rebuild

TPAE builds on Elementor's template library plus JSON import/export. There is **no remote design catalog** - the "catalog" is the **site's own saved templates** and known-good JSON you import. The catalog-first principle still holds: a saved section beats a scratch build on speed and consistency, so check the library before building a standard section from scratch.

## Reuse-first (before scratch-building a standard section)

1. `list-templates` - what the site already has. Filter by `type` (page · section · container) to match what you need.
2. If a saved template fits the intent **and** the committed design direction, `apply-template` at the target position (it inserts with fresh ids). Then customize via Editing discipline - never rebuild an applied template.
3. Scratch-build only when nothing fits, the user wants bespoke, or the section is site-specific (a form wired to this site, a dynamic query tied to its taxonomy).

## Match, then re-style to the kit

A saved or imported template carries its own colors/fonts/spacing. Before using it, check its look against the design direction. After applying, re-style to the site kit per the token contract (kit values verbatim) - never leave a template's mismatched values on the page. Ask once: keep the template's look, or align it to the kit? Only re-style on "align".

## Build the library (leave the site better)

After building a section the user likes, offer to `save-as-template` (a write - needs a yes) so it is reusable. Name it clearly; it then appears in `list-templates` for next time.

## Cross-site reuse (JSON)

`export-page` returns a page's full Elementor JSON. `import-template` drops a JSON structure into a page - **as a draft**, at a stated position. Verify on the **target** against its own kit values (the two sites' kits may differ), resolve any missing-widget/dependency gaps, and never silently work around an incompatible import.

## Theme templates are a different thing

Header / footer / single / archive / 404 / search templates are theme-builder templates, not library sections -> **tpae-theme-builder** (`create-theme-template` + `set-template-conditions`).

## Rules

1. Reuse order: site saved templates (`list-templates` -> `apply-template`) -> a known-good JSON (`import-template`) -> scratch build.
2. Match the template to the design direction + kit before using; re-style to the kit after (token contract).
3. Applied/imported templates are customized, never rebuilt.
4. Saving, importing, and applying to a page in scope are writes - draft + confirm (SKILL.md non-negotiables).
