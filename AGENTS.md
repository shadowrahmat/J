# Elementor and WordPress CSS development standards

These are mandatory user-provided standards for all current and future Elementor and WordPress development in this project.

## Stable custom selectors

- Never write or modify custom CSS that targets Elementor-generated widget, container, template, or page identifiers.
- Forbidden authored selectors include `.elementor-element-abc123`, `.elementor-1267`, `[data-id="abc123"]`, and other automatically generated identifiers.
- Use manually assigned, descriptive, reusable, component-specific CSS classes as the root selector for every component.
- Before adding a class, check whether the exact target widget or container already has a suitable custom class. Reuse it when appropriate.
- Assign missing custom classes through Elementor's saved **Advanced → CSS Classes** setting. Preserve any existing classes that serve other components or functionality.
- Stable HTML elements and plugin classes may be targeted beneath the custom root class.
- Prefer native Elementor controls for user-editable layout and styling. Do not manually edit Elementor's generated CSS files.

Example: assign `juhani-product-gallery` to the Product Images widget, then use:

```css
.juhani-product-gallery .woocommerce-product-gallery .flex-control-thumbs {
    gap: 6px;
}
```

## Workflow and scope

1. Identify the exact widget, container, or section requested.
2. Inspect its existing custom classes and styling sources.
3. Assign a suitable custom class if one does not already exist.
4. Write modular CSS scoped to that class, with desktop, tablet, and mobile behaviour.
5. Verify that only the intended component is affected and preserve unrelated widgets, templates, and global styles.
6. Check existing functionality and responsive behaviour. State any unavailable visual verification instead of claiming it was performed.

- Avoid unnecessary `!important` declarations.
- Do not introduce new generated selectors when extending legacy CSS. Convert the selectors within the requested scope to stable custom classes; do not refactor unrelated styles without authorization.
- Keep backups and preserve the user's current saved edits when changing Elementor data.

## Required implementation handoff

Whenever a task includes custom CSS, specify:

- **CSS Class:** the exact manually assigned class.
- **Class Location:** the exact Elementor widget or container receiving it.
- **CSS Code:** the complete authored CSS scoped to the custom class.
- **Implementation:** where the active CSS is saved or should be pasted. Verify the installed Elementor UI location; do not assume the tab name.

Reference copies must be distinguished from active styling sources and from automatically generated output files.
