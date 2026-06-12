# Luke Goulden — Design System

> **Real Change. Real Results. Built for Real Life.**
> A clarity brand, not a fitness brand. Editorial, premium, calm — built to
> *cut through the noise*.

This project is the living design system for **Luke Goulden**, an online
fitness & lifestyle coach for busy parents and professionals over 30. It
encodes the **G-Cut identity (Brand Book v3, 16 April 2026)** as tokens, fonts,
brand assets, reusable React components, foundation specimen cards and a
full website UI kit.

Link `styles.css` (the single entry point) and read components via
`window.LukeGouldenDesignSystem_c14a0f`.

---

## Who Luke is

Luke Goulden coaches **busy parents and professionals over 30** who want
lasting change, not quick fixes. The offer is habit-based coaching,
science-backed nutrition and strength training that *fits a real week*.

- **Positioning:** *For* busy parents and professionals over 30, *who* want
  lasting change not quick fixes, *we* deliver coaching that fits real life —
  *unlike* programmes built for people with unlimited time, energy or genetics.
- **We are not:** a quick-fix macro calculator, a celebrity transformation
  mill, a 12-week program, an influencer supplement brand, or a bodybuilding
  prep coach.
- **Manifesto:** *"Start where you are. Then keep going."* We don't sell
  transformations — we build lives, for the long game.
- **Six tenets:** Habit over hype · Evidence, always · Meet people where they
  are · Honesty before comfort · Train for decades · Results are earned.

### The G-Cut mark
A proprietary capital **G** whose internal crossbar extends into a bold
horizontal stroke — piercing the right side of the letter and emerging beyond
the outer curve. *The bar literally cuts through* — Luke's method made visible.
The mark is **monochrome only** (Deep Teal, Off-White, Black, or Coral), never
two-tone, never reconstructed from memory. It holds at 32px and at 10 metres.

---

## Sources (provenance)

The reader may not have access to these, but they are the origin of the system:

- **Brand Book v3 "G-Cut"** — `uploads/BRAND-SPECS.md` plus 32 rendered pages
  `uploads/p01…p32-*.png` (cover, manifesto, positioning, tenets, voice,
  concept, primary mark, lockups, scale, clearspace, variants, palette,
  typography, imagery, donts, stationery, digital, social, apparel, etc.).
  This is the **authoritative, most recent identity** and the basis of this system.
- **Live presence:** [lukegoulden.com](https://lukegoulden.com) ·
  Instagram [@lukegouldencoach](https://www.instagram.com/lukegouldencoach/)
- **Landing-page reference:** `uploads/1-dark-helen.html` — a real
  conversion landing page (content reused, reskinned into the G-Cut palette
  in the website UI kit).
- **Brand assets:** `uploads/logo-teal.svg` (an *LG monogram* — note: NOT the
  canonical mark), `uploads/Logo-Colour.svg` (a *legacy* blue-gradient logo),
  client/transformation photography, Manrope font binaries.
- **Campaign codebase:** mounted at `Luke Campagin/` (brand book build scripts,
  campaign deck/report PDFs, business plan, marketing skills, stock imagery).

> **Note on the two "old" logos:** `logo-teal.svg` renders as an *LG* monogram
> and `Logo-Colour.svg` is a legacy blue-gradient mark. Neither is the current
> identity. The canonical mark is the **pure G-Cut** (ring + crossbar), rebuilt
> here as clean geometry in `assets/logo/` and the `Logo` / `GCutMark` components.

---

## CONTENT FUNDAMENTALS — how Luke writes

The voice is **calm, clear, direct, premium and mature**. No hype, no
bro-science, no clutter. Every line should feel *easier to understand than the
category norm*.

**Casing & punctuation**
- Headlines and body are **sentence case**. Full stops are used as rhythm,
  even on fragments: *"Real change. Real results. Built for real life."*
- **ALL CAPS** is reserved for the wordmark, eyebrows/labels, the tagline, and
  button text — always tracked out. Never set long copy in caps.
- The em-dash — used for the cut/aside — is a signature beat.
- British spelling (programme, personalised, colour).

**Person & stance**
- Speaks to **"you"**, signs as **"we"** (the coaching practice) and
  occasionally **"I"** in founder voice (the manifesto).
- Honest and adult: *"We promise the work is worth it, not that there's no work."*

**Voice ladder — say / don't say** (from the brand book)

| Say ✓ | Don't ✗ |
|------|---------|
| "Consistency beats intensity." | "Go hard or go home." |
| "Small, sustainable change." | "Insane shredding protocol." |
| "Science-backed." | "Industry-disrupting breakthrough." |
| "Your body is evidence of your habits." | "Transform your body in 30 days." |
| "We coach adults." | "We push you past your limits." |
| "Strength that fits your week." | "No excuses — no days off." |

**Copy-fit rules**
- Headlines: 3–8 words, 2 lines ideal, 3 max.
- Subheads: 8–18 words. Body: 2–4 line blocks. Quote cards: under 18 words.
- CTAs: *Apply for coaching · Book a call · Start here · See how it works.*
- **No emoji.** Not part of the brand.

---

## VISUAL FOUNDATIONS

**Overall vibe:** editorial, structured, generous whitespace, premium
restraint. Minimalism over decoration; clarity over cleverness. *Would this
still look good in black and white?*

**Colour** — five colours, strict roles, never substitute.
- Deep Teal `#1A3C34` (primary / brand trust / headings),
  Warm Coral `#E05A3A` (accent — energy; **editorial & CTA only**, never long
  body), Sage Green `#84B59F` (secondary / calm — soft panels & tags),
  Off-White `#F7F5F0` (warm background), Charcoal `#1E1E1E` (body text).
- Default surface = Off-White with Charcoal text and Teal headings. Dark
  sections invert to Off-White on Teal. Coral is a *seasoning*, never the meal.

**Type** — one family, **Manrope** (geometric grotesque), two voices:
- Wordmark: Manrope Medium, ALL CAPS, **+0.22em** tracking.
- Headlines: Manrope Bold, sentence case, **−0.015em** (tight), line-height ~1.05.
- UI caps / eyebrows / tagline: tracked uppercase (+0.18–0.28em).
- Body: Manrope Regular, 16–18px, line-height 1.6, measure ≤ 66ch.
- *(Brand book references Montserrat for the wordmark + Inter for body; Manrope
  is the shipped web standard — see CAVEATS.)*

**Backgrounds & imagery** — full-bleed editorial **photography**, never
illustration or gradient wallpaper. Three categories: portraiture (natural
light, real clothes, never gym selfies), life in motion (real moments, not
posed), client proof (honest lighting, consistent crop, with permission).
Image mood is **warm and natural**; press logos may go greyscale. Text over
photos always sits on a **dark teal protection gradient** — never raw on a busy
image.

**Layout** — 12-column logic, strong left alignment, asymmetric balance, large
outer margins. Containers: text 42rem, content 72rem, wide 84rem. Section
rhythm `clamp(3.5rem, 7vw, 6rem)`.

**Corners & cards** — soft, never bubbly. Radii 4 → 16px (cards 12px, buttons
4px, pill for tags). Cards are white with a 1px hairline (`rgba(30,30,30,.12)`)
and a *very soft* teal-tinted shadow, or flat. Calm cards use the sage-soft
fill. Dark cards are solid teal.

**Shadows & depth** — extremely soft or none, always cool/teal-tinted
(`rgba(16,36,32,…)`). `xs → soft → card → lift`. No glows, bevels or hard drop
shadows.

**Borders & dividers** — 1px hairlines for structure; a **3px coral left rule**
marks testimonials and emphasis; thin sage rules on calm panels.

**Transparency & blur** — sticky header is off-white at ~86% with a 12px
backdrop blur. Modal scrims are teal at ~55% with a light blur. Used sparingly.

**Motion** — supports clarity, never shows off. Fade, slight translate, and a
**−1px hover lift** on buttons; durations 150–320ms on gentle easing
(`cubic-bezier(0.4,0,0.2,1)`). No bounce, no infinite loops, respect
`prefers-reduced-motion`.

**States** — hover: subtle background wash or darker teal + lift; press: settle
to translateY(0); focus: 2px **coral** outline, 2px offset; disabled: 0.45
opacity. Accordion plus-icon rotates 45° to a cross.

---

## ICONOGRAPHY

The brand has **no proprietary icon font**; the source landing pages used
hand-drawn inline line SVGs. This system standardises on a single, consistent
set of **geometric line icons** matching the minimal aesthetic:

- **Style:** 24×24 viewBox, **2px stroke**, round caps & joins, `currentColor`,
  no fills (except the rare solid star). This is the **Lucide** visual language
  (closest match to the brand's existing hand-drawn strokes). If you need an
  icon not bundled here, pull it from [Lucide](https://lucide.dev) — same
  weight and corner treatment — or the in-repo set in
  `ui_kits/website/icons.jsx` (arrow, check, cross, star, menu, close, call,
  plan, users, trophy).
- **Brand mark vs icons:** the G-Cut mark is **not** an icon — never mix it
  into an icon row or combine it with other symbols.
- **No emoji**, no unicode-glyph icons in product UI. The only glyph used
  decoratively is the star ★ in rating rows and the mid-dot · in taglines.
- *Substitution flagged:* Lucide is a substitution for the brand's bespoke
  hand-drawn icons (no official icon library was supplied).

---

## VISUAL ASSETS

In `assets/`:
- `logo/` — the **canonical G-Cut mark** rebuilt as clean geometry in five
  monochrome treatments (`gcut-mark-teal/white/coral/black/sage.svg`) plus a
  `currentColor` master (`gcut-mark.svg`). Use the `Logo` / `GCutMark`
  components in product code.
- `fonts/` — Manrope (variable + static 300–800).
- `img/` — Luke hero & portrait, lifestyle, five client transformations
  (Craig, Helen, Jono, Scott, Richie).

---

## INDEX

**Root**
- `styles.css` — single entry point (`@import`s tokens, base, components).
- `tokens/` — `colors.css` · `typography.css` · `spacing.css` · `fonts.css` ·
  `base.css` (reset + element defaults) · `components.css` (`.lg-*` classes).
- `readme.md` (this file) · `SKILL.md` (Agent-Skill wrapper).

**Components** — `window.LukeGouldenDesignSystem_c14a0f` · group dirs each carry
a `*.card.html` preview:
- `components/brand/` — **Logo**, **GCutMark**
- `components/buttons/` — **Button** (primary · coral · secondary · ghost · ondark)
- `components/forms/` — **Field**, **Input**, **Textarea**
- `components/data-display/` — **Badge**, **Tag**, **Avatar**, **Stat**
- `components/content/` — **Card**, **Testimonial**, **Accordion**, **TickItem**, **Transformation**

**Foundation cards** — `guidelines/cards/` (Design System tab):
Colors (palette, neutrals, ramps & tints) · Type (wordmark & display, scale,
body & caps) · Spacing (scale, radii, shadows, borders) · Brand (G-Cut mark,
lockups, voice, imagery).

**UI kit** — `ui_kits/website/` — full interactive coaching marketing site
(see its `README.md`).

---

## CAVEATS / OPEN QUESTIONS

1. **Typeface:** the brand book specifies **Montserrat** (wordmark) + **Inter**
   (body), but only **Manrope** font files were supplied. This system ships
   **Manrope** as the single web typeface (a close geometric grotesque). If you
   want the wordmark pixel-exact to the book, supply Montserrat and we'll add it
   for the lockup.
2. **Coral hex:** the book lists Warm Coral as `#E05A3A` in the spec table and
   palette page — used here. (Some older notes show `#E05A3A` vs `#E0593A`;
   confirm if it matters for print.)
3. **Corrupt assets:** the supplied press-publication logo PNGs (Men's Fitness,
   Coach, Women's Health, Balance, Health & Wellbeing) are **unreadable** at
   every source, so the website press strip is set as **text logotypes**. Send
   valid SVG/PNG logos to restore image badges.
4. **Icons** are **Lucide** as a substitution for the brand's bespoke
   hand-drawn line icons — confirm or supply an official set.
