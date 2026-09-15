# Taste

- Prefers minimal, targeted fixes over redesigns: when asked to fix a bug, explicitly wants the existing implementation unchanged and the exact issue corrected in-place rather than rebuilding it. Confidence: 0.9
- Prefers changes to be scoped so existing, working behavior is preserved (e.g., fixing a mobile-only bug must not alter the desktop experience), using responsive media queries / breakpoint scoping where needed. Confidence: 0.9
- Prefers diagnosing the root cause by inspecting the framework/library's native behavior (how the platform components position and animate) before overriding it, and prefers removing conflicting overrides over layering on more of them. Confidence: 0.7