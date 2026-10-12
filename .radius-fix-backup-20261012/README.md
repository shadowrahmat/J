Border radius cleanup — 2026-10-12

The `.bak` files preserve the custom PHP/CSS files before their forced radius declarations were removed. `elementor-meta.json` preserves the changed Elementor metadata before cleanup and native radius migration. No WordPress core or vendor plugin files were replaced.

The fix removes authored `border-radius: ... !important` locks. Rahmat retains its design and content while its corners use native Elementor settings. Existing manually entered radius values take precedence over the initial 12px default.
