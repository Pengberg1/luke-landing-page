# Handoff: Luke Goulden — Shopify Theme ("G-Cut")

## Overview
Build a **Shopify Online Store 2.0 theme** for **Luke Goulden Coaching** from the bundled landing-page design. The homepage is the hi-fi reference (every section already designed); the rest of the theme (product, collection, cart, page, blog, etc.) extends the same design system. The brand is **G-Cut** (Brand Book v3): calm, editorial, premium — a clarity brand, not a fitness brand.

Read **`DESIGN_SPEC.md` first** — it is the precise visual spec (tokens, section-by-section measurements, interactions, voice rules). This README only covers the Shopify translation.

## About the Design Files
The files in this bundle are **design references created in HTML** — a prototype showing intended look and behavior, not production code to ship directly. Your task is to **recreate this design as a Shopify theme** using Liquid, JSON templates, and the OS 2.0 section architecture. The HTML/CSS is clean, token-driven and dependency-free, so most of the CSS can be ported close-to-verbatim into theme assets; the markup must be rebuilt as Liquid sections with schemas so every piece of content is merchant-editable in the theme editor.

## Fidelity
- **Homepage: high-fidelity.** Recreate `Luke Goulden Landing Page.html` pixel-perfectly as a set of sections composed in `templates/index.json`. All values come from the bundled token CSS — do not hard-code approximations.
- **All other templates: low-fidelity guidance.** No mockups exist. Design them yourself *strictly inside* the token system and the layout rules in `DESIGN_SPEC.md` (editorial, generous whitespace, strong left alignment, hairlines, soft teal shadows, Manrope only, no emoji).

## Target & tooling
- Shopify **Online Store 2.0** theme: JSON templates, sections everywhere, app blocks supported on product templates.
- Scaffold from Shopify's official **Skeleton theme** (`shopify theme init --clone-url https://github.com/Shopify/skeleton-theme`) — NOT Dawn. Dawn carries too much styling to fight; this design wants a clean slate.
- Develop with **Shopify CLI** (`shopify theme dev`), lint with **Theme Check** (`shopify theme check`). Zero-warning target.
- No build step, no JS framework. Vanilla JS only (the whole reference page needs ~15 lines). No external CDNs — fonts and CSS ship in `assets/`.

## Theme structure

```
layout/theme.liquid
config/settings_schema.json, settings_data.json
locales/en.default.json
templates/index.json, product.json, collection.json, cart.json,
          page.json, page.contact.json, blog.json, article.json,
          search.json, 404.json, customers/*.liquid|json
sections/  (see mapping below)
snippets/  gcut-mark.liquid, icon.liquid, tick-item.liquid, price.liquid
assets/    lg-tokens.css, lg-base.css, lg-components.css, theme.css, theme.js,
           manrope-*.woff2, luke-hero.jpg, lifestyle.jpg, tf-*.jpg
```

### Assets pipeline (important quirks)
- Shopify's `assets/` directory is **flat** — no subfolders. Flatten the bundled `_ds/.../tokens/*.css` into prefixed files (`lg-colors.css` etc.) or concatenate into one `lg-tokens.css`. Load with `{{ 'lg-tokens.css' | asset_url | stylesheet_tag }}`.
- Rewrite `tokens/fonts.css` `@font-face` rules to use `{% raw %}{{ 'manrope-regular.woff2' | asset_url }}{% endraw %}` — easiest as a `{% style %}` block in `theme.liquid` or a `fonts.css.liquid` asset. Font binaries are in `_ds/.../assets/fonts/` (use the static woff2 weights 300–800, or the variable font + static fallbacks).
- **Do not use Shopify's `font_picker`** for the body/headings defaults — the brand is Manrope-only, self-hosted. It's fine to omit font settings entirely.
- Demo photography (`assets/` in this bundle) is for development/demo-store use; production images are uploaded by the merchant through section image pickers.

## Global settings (`config/settings_schema.json`)
Expose the brand tokens as theme settings **with the brand values as defaults**, and wire them to CSS custom properties in a `{% style %}` block in `theme.liquid` so the token CSS picks them up:

| Setting | Type | Default |
|---|---|---|
| `color_teal` (Primary / Deep Teal) | `color` | `#1A3C34` |
| `color_coral` (Accent / Warm Coral — CTA only) | `color` | `#E05A3A` |
| `color_sage` (Secondary / Sage Green) | `color` | `#84B59F` |
| `color_offwhite` (Background) | `color` | `#F7F5F0` |
| `color_charcoal` (Body text) | `color` | `#1E1E1E` |
| `contact_url` (Apply-for-coaching link) | `url` | `/pages/contact` |
| socials (Instagram, Trustpilot, Apple Podcasts, Spotify) | `url` | — |

Derived steps (`--lg-teal-600/800/900`, overlays, shadows) should be computed with `color_modify`/oklch in the style block or left as static values in `lg-tokens.css` — don't expose them as settings.

## Homepage → section mapping
Each `data-screen-label` in the reference HTML becomes one section. Composition order lives in `templates/index.json`. All copy below is the **default** value for each setting (exact strings in the HTML / `DESIGN_SPEC.md`).

| Reference section | Section file | Settings | Blocks |
|---|---|---|---|
| Header (sticky, blurred off-white) | `sections/header.liquid` (header group) | logo behavior is fixed (G-Cut mark + wordmark snippet); menu via `link_list` | — |
| Hero (deep teal, 2-col) | `sections/hero.liquid` | eyebrow, heading, cut-line, subtext, CTA label+link, note, rating line, image picker | `stat` (number, label) ×3; `before_after_card` (image, name, result, duration, quote) ×0–1 |
| Sound familiar | `sections/pain-points.liquid` | eyebrow, heading, image picker | `tick` (text) ×6 |
| Imagine if… | `sections/imagine.liquid` (sage calm surface) | heading, kicker line 1, kicker line 2 (bold) | `item` (icon select, text) ×5 |
| Results | `sections/transformations.liquid` | eyebrow, heading | `transformation` (image, name, result, duration) ×5 |
| Reviews bar | `sections/social-proof.liquid` | — | `rating` (logotype text) and `listen` (label, icon select, url) |
| How it works | `sections/how-it-works.liquid` | heading | `step` (icon select, title, text) ×4, auto-numbered with `forloop.index` |
| What's included / different | `sections/included.liquid` | left heading, right heading, tagline | `include_item` (text) ×9; `exclude_item` (text) ×4. Phone mockup = static snippet `phone-mock.liquid` (CSS-only; optionally a `show_phone` checkbox) |
| FAQ + closing CTA (teal) | `sections/faq-cta.liquid` | faq heading, image picker, eyebrow, heading line 1, heading line 2 (sage), text, CTA label+link, note | `question` (question, richtext answer) ×6 |
| Footer (teal-900) | `sections/footer.liquid` (footer group) | copyright text, policy menu `link_list` | — |

Reuse `pain-points`, `transformations`, `social-proof`, `faq-cta` etc. as generic sections merchants can add to any page — set sensible `presets` so they appear in the "Add section" picker.

### Example section skeleton (hero)

```liquid
{{ 'section-hero.css' | asset_url | stylesheet_tag }}

<section class="ll-hero" id="top">
  <div class="lg-container">
    <div class="ll-hero-grid">
      <div class="ll-hero-copy">
        {%- if section.settings.eyebrow != blank -%}
          <p class="lg-eyebrow">{{ section.settings.eyebrow }}</p>
        {%- endif -%}
        <h1>{{ section.settings.heading }}<span class="ll-cut">{{ section.settings.cut_line }}</span></h1>
        <p class="ll-sub">{{ section.settings.subtext }}</p>
        <div class="ll-stats">
          {%- for block in section.blocks -%}
            {%- if block.type == 'stat' -%}
              <div class="lg-stat lg-stat--ondark" {{ block.shopify_attributes }}>
                <span class="lg-stat__num">{{ block.settings.number }}</span>
                <span class="lg-stat__label">{{ block.settings.label }}</span>
              </div>
            {%- endif -%}
          {%- endfor -%}
        </div>
        <div class="ll-actions">
          <a class="lg-btn lg-btn--coral lg-btn--lg" href="{{ section.settings.cta_link | default: settings.contact_url }}">{{ section.settings.cta_label }}</a>
          <span class="ll-note">{{ section.settings.note }}</span>
        </div>
      </div>
      <div class="ll-hero-media" {% if section.settings.image %}style="background-image:url('{{ section.settings.image | image_url: width: 1400 }}')"{% endif %}>
        {%- for block in section.blocks -%}
          {%- if block.type == 'before_after_card' -%}
            {%- render 'before-after-card', block: block -%}
          {%- endif -%}
        {%- endfor -%}
      </div>
    </div>
  </div>
</section>

{% schema %}
{
  "name": "Hero",
  "settings": [
    { "type": "text", "id": "eyebrow", "label": "Eyebrow", "default": "For busy parents & professionals over 30" },
    { "type": "text", "id": "heading", "label": "Heading", "default": "Leaner. Stronger. More confident." },
    { "type": "text", "id": "cut_line", "label": "Cut line (sage)", "default": "— without living in the gym." },
    { "type": "textarea", "id": "subtext", "label": "Subtext" },
    { "type": "text", "id": "cta_label", "label": "CTA label", "default": "Apply for coaching" },
    { "type": "url", "id": "cta_link", "label": "CTA link" },
    { "type": "text", "id": "note", "label": "CTA note", "default": "Spots are limited — apply today." },
    { "type": "image_picker", "id": "image", "label": "Hero photo" }
  ],
  "blocks": [
    { "type": "stat", "name": "Stat", "settings": [
      { "type": "text", "id": "number", "label": "Number" },
      { "type": "text", "id": "label", "label": "Label" } ] },
    { "type": "before_after_card", "name": "Before/after card", "limit": 1, "settings": [
      { "type": "image_picker", "id": "image", "label": "Before/after image" },
      { "type": "text", "id": "name", "label": "Name" },
      { "type": "text", "id": "result", "label": "Result", "default": "−11kg" },
      { "type": "text", "id": "duration", "label": "Duration", "default": "12 weeks" },
      { "type": "text", "id": "quote", "label": "Quote" } ] }
  ],
  "presets": [{ "name": "Hero" }]
}
{% endschema %}
```

Follow the same pattern for every section. Icons: port the inline 24×24 / 2px-stroke line SVGs from the reference HTML into an `icon.liquid` snippet (`{% render 'icon', name: 'target' %}`); icon-select settings use a `select` whose options are the snippet's names.

## Commerce templates (no mockup — extend the system)
Luke sells **coaching, not boxed goods** — products are coaching programmes (e.g. "1:1 Coaching — 12 weeks", monthly plans via subscription apps, maybe digital guides). Keep commerce surfaces calm and editorial:

- **`product.json` / `sections/main-product.liquid`** — two-column: media gallery left (12px-rounded images, hairline border), right: eyebrow (product type), product title as headline, price (Manrope extrabold, teal), short description, variant picker (radio pills styled like the brand `Tag`), quantity only if relevant, **buy button = `lg-btn--coral lg-btn--lg`** ("Apply for coaching" / "Start here" — label from settings), accordion blocks for "What's included" / FAQ reusing `lg-accordion` styles, `@app` block support. Tick-lists for programme contents. No reviews-app slop unless added by merchant.
- **`collection.json`** — eyebrow + heading, product cards: image (4:5, rounded 12px, hairline), title, price, optional sage `Tag`. 3-col grid, generous gaps. No quick-buy overlays.
- **`cart.json`** — quiet table on off-white: line items with thumbnails, hairline row dividers, subtotal, coral checkout button. Empty state: "Your cart is empty. Start where you are." + ghost button to the coaching page.
- **`page.json`** — narrow text measure (42rem container `--w-text`), teal headings, supports any homepage section added below.
- **`page.contact.json`** — the "Apply for coaching" destination: short intro + `{% form 'contact' %}` using the design system's calm `Field`/`Input`/`Textarea` styles (see `_ds/.../tokens/components.css`), coral submit.
- **`blog.json` / `article.json`** — editorial list and article layout; article body at ≤66ch, 1.6 line-height.
- **`search.json`, `404.json`, `customers/*`** — minimal, on-token. 404: teal section, "This page doesn't exist. Your plan still does." + ghost button home. Use Shopify's default customer templates restyled with tokens.
- **Cart behavior:** page-based cart is fine (calm brand, tiny catalog). No drawer/AJAX cart required; if added later, modal scrim = teal at ~55% with light blur.

## Interactions & behavior (port as-is)
- Sticky header, off-white 86% + 12px backdrop blur, hairline bottom border.
- Mobile menu at ≤64em: burger toggles a dropdown panel; keep `aria-expanded` in sync (vanilla JS in `assets/theme.js`).
- FAQ accordion: native `<details>/<summary>`, plus icon rotates 45° when open. No JS.
- Buttons: −1px hover lift, settle on press, 150–320ms, `cubic-bezier(0.4,0,0.2,1)`. Focus: 2px coral outline, 2px offset.
- `prefers-reduced-motion` respected (no smooth scroll, no lifts).
- Breakpoints 64em and 36em per `DESIGN_SPEC.md`. No infinite animations anywhere.

## Design tokens
All in `_ds/luke-goulden-design-system-…/tokens/*.css` — port verbatim. Summary: Deep Teal `#1A3C34` · Warm Coral `#E05A3A` (CTA/accent **only**, never long text) · Sage `#84B59F` · Off-White `#F7F5F0` · Charcoal `#1E1E1E`. Manrope only. Radii 4px (buttons) / 12px (cards) / 16px (image masks) / pill (tags). Hairlines `rgba(30,30,30,.12)`; shadows soft and teal-tinted. Full detail in `DESIGN_SPEC.md`.

## Brand mark — IMPORTANT
Use `brand/gcut-mark.svg` (or the inline SVG in the reference header/footer) as the canonical G-Cut mark — a G-ring **cut open on the right** with the bar piercing through. The copies under `_ds/.../assets/logo/` are an **outdated closed-ring version**; do not use them. Monochrome only (teal, off-white, black, coral). Render via a `gcut-mark.liquid` snippet with `currentColor`. Do not add a logo image-picker default that replaces the mark — but a merchant logo upload setting is fine as an override.

## Voice rules for any copy you write (defaults, locales, empty states)
Sentence case everywhere except tracked-caps labels/buttons. Full stops as rhythm ("Real change. Real results."). Speaks to "you", signs as "we". British spelling (programme, personalised). No hype, **no emoji**. CTAs from: *Apply for coaching · Book a call · Start here · See how it works.* Put all theme strings in `locales/en.default.json`.

## Definition of done
1. `shopify theme check` passes clean; theme uploads without errors.
2. Homepage in the theme editor reproduces the reference HTML pixel-close at 1440px, 1024px, 768px, 375px.
3. Every text string, image and list item on the homepage is editable via section/block settings without touching code.
4. Product → cart → checkout flow works with a demo "12-week coaching" product.
5. Lighthouse: no render-blocking third-party requests, fonts self-hosted with `font-display: swap`.

## Files in this bundle
| File | What it is |
|---|---|
| `DESIGN_SPEC.md` | **Precise visual spec** — tokens, per-section measurements, interactions, voice. Read first. |
| `Luke Goulden Landing Page.html` | The canonical homepage design. Opens directly in a browser. |
| `_ds/luke-goulden-design-system-…/` | Token CSS, component CSS, Manrope binaries, brand guide (`readme.md`). |
| `assets/` | Demo photography (hero, lifestyle, 5 transformations). |
| `brand/gcut-mark.svg` | Canonical G-Cut mark (`currentColor`). `gcut-mark-source.png` is the client source it was measured from. |
