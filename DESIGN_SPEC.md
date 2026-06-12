# Handoff: Luke Goulden — Coaching Landing Page

## Overview
A single-page conversion landing page for **Luke Goulden Coaching** (lukegoulden.com) — an online fitness & lifestyle coach for busy parents and professionals over 30. The page drives one action: **Apply for coaching** (links to `https://lukegoulden.com/contact/`). It follows the **G-Cut brand identity** (Brand Book v3): calm, editorial, premium — a clarity brand, not a fitness brand.

## About the Design Files
The files in this bundle are **design references created in HTML** — a prototype showing intended look and behavior, not production code to ship directly. Your task is to **recreate this design in your target codebase's environment** (Next.js, plain HTML/CSS, WordPress, etc.) using its established patterns. If no environment exists yet, choose the most appropriate stack for a static marketing page and implement the design there.

That said, the HTML/CSS here is clean, token-driven, and dependency-free (no JS framework — one small vanilla script for the mobile menu). Porting it close-to-verbatim is a legitimate option.

## Fidelity
**High-fidelity.** Final colors, typography, spacing, copy, imagery, and interactions. Recreate pixel-perfectly. All values are expressed as CSS custom properties from the bundled design system — use those tokens, do not hard-code approximations.

## Files
| File | What it is |
|---|---|
| `Luke Goulden Landing Page.html` | **The canonical design.** Page-level layout CSS in a `<style>` block (namespaced `.ll-*`), semantic markup, vanilla JS for mobile nav. Open directly in a browser — relative paths to `_ds/` and `assets/` work as-is. |
| `_ds/luke-goulden-design-system-…/` | The bound **Luke Goulden Design System**: token CSS files (`tokens/colors.css`, `typography.css`, `spacing.css`, `fonts.css`, `base.css`, `components.css`), `styles.css` entry point, Manrope font binaries, and `readme.md` (full brand guide — read it). |
| `assets/` | Photography: `luke-hero.jpg` (hero/closing background), `lifestyle.jpg`, and five transformation photos `tf-craig/helen/jono/scott/richie.jpg`. |
| `brand/gcut-mark.svg` | The **corrected canonical G-Cut mark** as a `currentColor` SVG (see Brand mark below). |
| `brand/gcut-mark-source.png` | The client-supplied source logo the corrected geometry was measured from. |

## Brand mark — IMPORTANT
The G-Cut mark is a capital-G ring whose **right side is cut open**, with a bold horizontal bar emerging through the gap and extending beyond the outer curve. It is **not** a closed circle with a bar overlaid.

Canonical geometry (already inline in the HTML header & footer, and in `brand/gcut-mark.svg`):

```svg
<svg viewBox="76 78 298 237" fill="currentColor">
  <path d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
  <rect x="171" y="179" width="203" height="36"/>
</svg>
```

- Ring: outer r 118.5, inner r 81.5 (stroke ≈ 37 units), centered (194.5, 196.5), open on the right between y 155–238.
- Bar: 203×36 units, vertically centered on the ring, ends flush with the ring's outer right tangent.
- **Monochrome only** — Deep Teal, Off-White, Black, or Coral. Never two-tone, never redrawn from memory.
- ⚠️ Note: `_ds/…/assets/logo/gcut-mark*.svg` in the design-system folder still carry an **outdated closed-ring version**. Use `brand/gcut-mark.svg` or the inline SVG from the page instead.

## Design Tokens
All tokens live in `_ds/…/tokens/*.css`. Key values:

**Colors** — Deep Teal `#1A3C34` (primary, headings, dark sections) · Warm Coral `#E05A3A` (CTA/accent only) · Sage Green `#84B59F` (calm secondary, hero sub-headline, stars) · Off-White `#F7F5F0` (page surface) · Charcoal `#1E1E1E` (body text). Darker teal steps (`--lg-teal-800/900`) for phone mock and footer.

**Type** — **Manrope** only (variable + static 300–800, bundled in `_ds/…/assets/fonts/`).
- Wordmark/eyebrows/buttons: ALL CAPS, tracked out (`--tr-wordmark` +0.22em, `--tr-caps` ≈ +0.18em), Medium–Bold.
- Headlines: Bold/Extrabold, sentence case, tight (−0.015em), line-height ~1.05.
- Body: Regular 16–18px, line-height 1.6, measure ≤ 66ch. British spelling. **No emoji.**

**Spacing/radius/shadows** — see `tokens/spacing.css`. Section rhythm `--section-y` ≈ clamp(3.5–6rem). Radii: 4px buttons, 12px cards (`--radius-lg`), 16px+ image masks (`--radius-xl`), pill for tags. Shadows extremely soft, teal-tinted (`--shadow-soft`, `--shadow-lift`). Hairline borders `rgba(30,30,30,.12)`.

## Page structure (top → bottom)
Every section is marked with `data-screen-label` in the HTML.

1. **Header** (sticky) — off-white at 86% opacity + 12px backdrop blur, hairline bottom border. Left: G-Cut mark (1.45rem tall) + "LUKE GOULDEN" wordmark in teal. Center: 5 caps nav links (Coaching / Results / How it works / Reviews / FAQ), hover → coral. Right: small primary (teal) button "Apply for coaching". Below 64em: nav and button collapse into a burger menu that drops an off-white panel below the header.
2. **Hero** (solid Deep Teal) — two-column grid (1.05fr/.95fr). Left: sage eyebrow, H1 "Leaner. Stronger. More confident." with a sage sub-line "— without living in the gym.", sub-paragraph, 3 stat blocks (100+ clients, 100+ five-star reviews, lasting results), coral large CTA + "Spots are limited" note, 4.9/5 Trustpilot rating row with sage stars. Right: photo panel (`luke-hero.jpg`, top-rounded 16px, dark-teal protection gradient from 40% to bottom) with a floating white **before/after card** (Helen, −11kg, 12 weeks, quote) top-right, shadow `--shadow-lift`.
3. **Sound familiar** (white card surface) — two-column: 6 tick-list pain points (`lg-tick` circle-check icons, 2-col grid) + `lifestyle.jpg` rounded 16px.
4. **Imagine if…** (sage-soft calm surface, centered) — 5 columns, each a 2.5rem teal line icon + one short line. Kicker: "That's what coaching is all about. **Realistic plans. Real support. Real results.**"
5. **Results** (off-white) — eyebrow "Client proof", H2 "Real people. Real results.", 5-column grid of `lg-transform` figures (photo + name / result / duration captions): Craig −16.6kg/12 wks, Helen −11kg/12 wks, Jono −50lbs+/6 mo, Scott −45lbs/5 mo, Richie −14lbs/8 wks.
6. **Reviews bar** (white, hairline top+bottom) — Facebook / Google / Trustpilot logotypes each over 5 coral stars, plus "Listen on Apple Podcasts" / "Listen on Spotify" with line icons.
7. **How it works** (white card surface) — 4 centered steps, each: coral-outlined number pill, 2.4rem teal icon, H3, one line. Thin connector dash between steps (hidden when wrapped).
8. **What's included / Why we're different** (off-white) — 3-column grid (1fr / 17rem / 1fr): 9 tick items · a **phone mockup** (CSS-only, dark-teal body, 4 app cards: Push day, Nutrition, Check-in, Steps) · 4 cross-items ("No quick fixes…") + a coral-top-rule tagline.
9. **FAQ + closing CTA** (full-bleed Deep Teal, 3 columns 1.1fr/.8fr/1.1fr) — left: 6-item accordion (native `<details>`, plus-icon rotates 45° on open via CSS); middle: full-height photo column (`luke-hero.jpg` + teal gradient scrim); right: eyebrow "Start where you are. Then keep going.", H3 "Stop starting over. / You deserve better." (second line sage), coral CTA.
10. **Footer** (teal-900) — G-Cut mark + wordmark in off-white, © line, Privacy / Terms links.

## Interactions & Behavior
- **Sticky header** with backdrop blur; anchor links scroll to sections (`scroll-behavior: smooth`, disabled under `prefers-reduced-motion`).
- **Mobile menu**: burger toggles `.ll-open` on the nav list; `aria-expanded` kept in sync; closes when a link is clicked. (~15 lines of vanilla JS at the bottom of the file.)
- **Accordion**: native `<details>/<summary>` — no JS. Icon rotates 45° to a cross when open.
- **Buttons**: −1px hover lift, settle on press; durations 150–320ms, easing `cubic-bezier(0.4,0,0.2,1)`. Focus = 2px coral outline, 2px offset.
- All CTAs link out to `https://lukegoulden.com/contact/` (new tab).
- **Responsive breakpoints**: 64em (single-column hero, 2-col grids, burger nav, phone mock moves first) and 36em (single-column lists, before/after card becomes static full-width). No other JS, no analytics, no form on this page.

## State Management
None required — static page. Only UI state is the mobile-menu open flag and native accordion open state.

## Voice rules for any copy changes
Sentence case everywhere except tracked-caps labels. Full stops as rhythm ("Real change. Real results."). Speaks to "you", signs as "we". No hype, no emoji, British spelling. CTAs from the approved set: *Apply for coaching · Book a call · Start here · See how it works.*
