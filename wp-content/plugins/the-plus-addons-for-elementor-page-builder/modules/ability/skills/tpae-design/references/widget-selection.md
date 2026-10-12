# Widget selection - intent -> TPAE widget

Mapped to the live 160-ability roster (v6.5.2): 27 free widget abilities (`tpae/tpae-*`) + 83 Pro (`tpaep/tpaep-*`). **`list-widgets` is always authoritative** for what THIS site can build - this map supplies routing judgment, not existence claims. Prefer a TPAE widget over a generic core widget when one fits; use core when it's the simpler right tool. Read a widget's schema (`get-widget-schema`) before writing settings.

`(Pro)` = `tpaep/` ability, Pro sites only. `[dep: X]` = needs plugin X active. **->skill** = that domain has a dedicated skill; load it for depth.

## Free widgets (`tpae/tpae-*`) by intent

**Text & headings**
| Intent | Widget |
|---|---|
| Rich text / paragraph | Text Block (`tpae-text-block`) |
| Pull-quote / styled quote | Blockquote (`tpae-blockquote`) |
| Typing / rotating / animated heading | Heading Animation (`tpae-heading-animation`) |
| Code block with syntax colors | Syntax Highlighter (`tpae-syntax-highlighter`) |

**Media & interaction**
| Intent | Widget |
|---|---|
| Icon (styled/animated) | Icon (`tpae-icon`) |
| Video (self / YouTube / Vimeo, overlay) | Video Player (`tpae-video-player`) |
| Hover-reveal card | Hover Card (`tpae-hovercard`) |
| Embed a social post | Social Embed (`tpae-social-embed`) |
| Callout / alert / notice | Message Box (`tpae-messagebox`) |
| Animated stat / skill bar | Progress Bar (`tpae-progress-bar`) |

**Navigation & page behavior**
| Intent | Widget |
|---|---|
| Simple nav menu | Navigation Menu Lite (`tpae-navigation-menu-lite`) - mega is Pro, ->tpae-theme-builder |
| Smooth anchor scrolling | Smooth Scroll (`tpae-smooth-scroll`) |
| Light/dark toggle | Dark Mode (`tpae-dark-mode`) |
| Age confirmation gate | Age Gate (`tpae-age-gate`) |

**Forms** (each needs its plugin active)
| Intent | Widget |
|---|---|
| Contact Form 7 styled | `tpae-contact-form-7` [dep: CF7] |
| Gravity Forms styled | `tpae-gravity-form` [dep: Gravity] |
| Ninja Forms styled | `tpae-ninja-form` [dep: Ninja] |
| WPForms styled | `tpae-wp-forms` [dep: WPForms] |
| Everest Forms styled | `tpae-everest-form` [dep: Everest] |
| Booking / scheduler | Meeting Scheduler (`tpae-meeting-scheduler`) |

**Dynamic / theme-builder (single & archive)** - Post Title/Content/Meta/Featured Image/Author/Comment/Navigation (`tpae-post-*`). For the templates themselves -> **tpae-theme-builder**.

## Pro widgets (`tpaep/tpaep-*`, 83) by intent - routing

**Social proof & people** - Testimonial Listout · Team Member Listout · Clients Listout · Social Reviews · Number Counter.
**Content & listings** - Blog Listout · Gallery Listout · Product Listout · Dynamic Listing · Dynamic Smart Showcase · Style Lists · Carousel Anything · Carousel Remote.
**Pricing & commerce** - Pricing Table · Pricing List · Coupon Code · Countdown · WooCommerce set -> **tpae-woocommerce**.
**Motion / signature interaction** - Before After · Flip Box · Hotspot · Morphing Layouts · Horizontal Scroll Advance · Scroll Sequence · Mouse Cursor · Draw SVG · Circle Menu · Cascading Image · Image Factory · Timeline · Unfold · Off Canvas -> **tpae-motion**.
**Nav & site chrome** - Navigation Menu (mega) · Mobile Menu · Header Extras · Site Logo · Search Bar · Search Filter · Breadcrumbs Bar · Table of Content · Scroll Navigation -> **tpae-theme-builder**.
**Structure & effect** - Shape Divider · Row Background · Pre Loader · Advertisement Banner · Animated Service Boxes · Tabs Tours · Table · Chart · Switcher · Info Box · Advanced Buttons · Advanced Typography · Process Steps.
**Integrations & marketing** - Mailchimp [dep] · Google Map · WP Bodymovin (Lottie) · Audio Player · Social Feed/Icon/Sharing · Plus Form · Protected Content · WP Login Register · Custom Field · Dynamic Categories · Dynamic Device.

## Free -> Pro fallbacks at the boundary

Deliver the free build first, then the single capped Pro mention:
- Testimonials -> core / `tpae-blockquote` cards instead of `tpaep-testimonial-listout`.
- Team -> a container grid of `tpae-icon` + `tpae-text-block` cards instead of `tpaep-team-member-listout`.
- Info box -> `tpae-messagebox` instead of `tpaep-info-box`.
- Mega menu -> `tpae-navigation-menu-lite` instead of `tpaep-navigation-menu`.
- Counters -> `tpae-progress-bar` instead of `tpaep-number-counter`.

## Selection rules

0. **Confirm it's a TPAE widget.** Its Elementor name must start with `tp-` (`tp-pricing-table`, `tp-icon`...). A `premium-addon-*` name is Premium Addons for Elementor (a different plugin) - never present it as TPAE. If the widget isn't in `list-widgets`, it's disabled in the widget manager, not missing - offer to enable it (see `troubleshooting`).
1. Read the widget's live schema (`get-widget-schema`) before writing settings.
2. One widget per job; don't stack two to fake a capability a single widget has.
3. If no TPAE widget fits, use the Elementor core widget without apology.
4. If the mapped widget is Disabled or its dependency is inactive, follow SKILL.md availability states - never silently substitute.
