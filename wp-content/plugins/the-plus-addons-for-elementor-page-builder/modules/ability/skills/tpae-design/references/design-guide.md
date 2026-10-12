# The Plus Addons - Page Design Guide

This is how a page built with The Plus Addons should look and feel. Follow it for every build; it governs design judgment where SKILL.md governs process.

## The TPAE point of view

A Plus-built page looks **deliberately made, and like it belongs to the site it's on.** Five commitments, in priority order:

1. **Fit before flair.** The page inherits the site's kit - its colors, type, spacing, radius - and reads as part of it. The addon's value is *range*, not a signature look imposed on every client. If someone can tell "an addon built this" from the styling alone, that's a failure.
2. **Earn every effect.** TPAE ships the deepest motion and interaction library of any Elementor addon. The discipline is using almost none of it. **One signature moment per page.** Restraint is the flex - a composed, still page beats a busy one.
3. **Real over decorative.** Real copy, real numbers, real images, real structure. No filler, no invented precision, no fake UI chrome, no ornament standing in for content.
4. **Tokens are law.** The Elementor kit is the single source of truth. A Plus page never scatters one-off colors, sizes, or spacings. Reuse the kit's values, or add to the kit - never freelance.
5. **Finished, not flashy.** The bar is "a professional made this on purpose," not "this is impressive." Composed, legible, and complete beats loud every time.

The floor is clearing the machine-built look - an ad-hoc color and size in every section, a centered mega-hero, three identical icon boxes, vague copy, filler images. Clearing it is the start, not the finish.

## Read before you build

Every build, call first:
- `get-global-settings` - the kit's colors, typography, content width, spacing.
- `get-page-structure` - the element tree and ids (existing pages).
- `list-widgets` - what this install actually has (free + Pro, minus disabled).

Then, before placing anything:
- **Ground it.** Name the business, its audience, and this page's one job. Design for that.
- **Match, don't reinvent.** Derive the direction from the globals and pages you just read. Propose a fresh look only when nothing coherent exists to match, or the user asks.
- **Stay scoped.** A request for a section yields a section. Never restyle the rest of the site as a side effect - offer it, don't do it.
- **Plan, critique, then build.** Draft a small token set (palette, type scale, spacing, one signature idea) and a layout. Re-read it against the brief; cut anything you'd produce for any page. Then build to the plan.

## The token contract (TPAE can write the kit)

The Elementor kit is the token layer. Take a small palette, type scale, and spacing scale from `get-global-settings` and reuse those exact values across the page. Scattered one-off values are the single largest reason these pages look unfinished.

TPAE can *write* the kit with `update-global-colors` and `update-global-typography` - a real advantage. So when the design needs a token the kit lacks: ask the user once -> add the token to the kit -> reference it. Fall back to raw per-control values only if they decline; then tell them which values to register in Site Settings so the page stays editable.

## The dials

Infer these from the subject and state them:
- **variance** - low is symmetric and composed; high is asymmetric, offset, split. Low is a finished result, not a shortfall. Default toward low.
- **density** - low opens whitespace and leading; high tightens both.
- **motion** - low is static and complete; high earns entrance and scroll movement. **Default low.** A deep motion library is a reason to ration harder, not more freely (the tpae-motion skill governs the how).
- **match vs new** - default match; go new only when asked.

## Build with the editor's own tools only

- **Containers, not sections or columns.** `add-container`, then place TPAE widgets by their per-widget ability. Read a widget's keys with `get-widget-schema` before writing them.
- **Declare the mobile collapse** for every multi-column container.
- **Widget settings and options over injected code.** `add-custom-css` / `add-custom-js` are a last resort behind explicit consent (SKILL.md non-negotiable 4).
- **Dynamic content is bound, not typed.** `set-dynamic-tag` from `list-dynamic-tags` for posts, archives, and Woo - never hard-coded strings.
- **Fit the tier.** Elementor core + TPAE free widgets by default; a Pro widget only when it's clearly the better tool and the install has it.

## Confirm by building

Build the first real section - usually the hero - as the visual check and the finished quality bar, not a rough draft. Judge the front-end rendering, not the stored settings.

## Layout (hard rules)

- Hero fits the first viewport: headline ≤2 lines, short subtext, primary action visible without scrolling. A four-line headline is a size error.
- The hero is one moment, ~4 text elements at most. Logo walls, stats, and bullets go in sections below.
- A layout family appears once. Never a row of three identical cards - use an uneven split, a varied grid, or a carousel. At most two image-and-text splits in a row.
- Eyebrows rationed: at most one small uppercase label per three sections.
- One message per section. Navigation stays one line at desktop.

## Color, type, shape

- One accent, held across the page. Neutrals biased slightly toward the accent's hue, one temperature. Never pure black for text or grounds.
- Display leading 1.0-1.15; body measure 60-70 characters; leading tracks the density dial. Build hierarchy with weight and color, not size alone.
- Elevation must mean something: a card for real hierarchy, else a divider or space. Tint shadows to the ground - no pure-black shadow. Lock one corner-radius scale.
- At most two font families. If the kit already defines type, use it - don't introduce a third voice.

## Motion

TPAE owns a large motion vocabulary; use it like a scalpel. Motion only for hierarchy, feedback, state, or the one signature moment per page. A static page is complete. Honor reduced-motion. One signature effect used sparingly beats five used everywhere. (Depth: the tpae-motion skill.)

## Images

Prefer the site's own media. `search-images` (Openverse) + `sideload-image` for licensed stock - with consent. Until a source is chosen, never leave an empty slot - use an on-palette gradient/block composed like the final image. Never pass a stock photo off as a real product shot.

## Copy

Words are design material. Active voice; name things by what the person controls, not the system. One label per action, kept through the flow. An error says what happened and how to fix it; an empty state shows how to fill it. One register per page. Real numbers only.

## Self-audit before finishing

Re-check the rendered page and fix: any color/font/spacing off the token set; more than two font families; contrast below AA; an over-long H1; three identical cards; critical controls unset; more than one signature motion moment. Then cut what a text scan can't catch: numbered eyebrows/step labels, version/status badges, locale/time/weather strips, scroll cues, fake product UI built from plain elements, decorative craft labels, mock-poetic strings, mixed copy registers.
