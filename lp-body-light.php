<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= strip_tags(html_entity_decode($T['headline'])) ?> — Luke Goulden Coaching</title>
<meta name="description" content="Coaching for women who are done starting over. A plan built around your real week, with a coach who keeps you going.">
<link rel="stylesheet" href="/_ds/luke-goulden-design-system-c14a0f1f-c08a-4904-9234-32785b9e3ab9/tokens/fonts.css">
<?php
/**
 * The LIGHT build.
 *
 * Not the dark page with different hex codes. The dark build sells with
 * intensity — black, gold, "APPLY", before-and-afters shouting weight lost.
 * That register works on cold male traffic and works against us here.
 *
 * So this page is quieter and roomier: warm off-white, one accent, big type,
 * a lot of air. It leads with what changes in her week rather than what she
 * weighs, puts the female result first, and lets written words carry proof
 * while the photo library is still mostly men.
 *
 * Every colour, word and image still comes from the variant — it is editable in
 * /admin.php exactly like the others, and it reports its own click rate.
 */
$A   = $C['accent'];      // terracotta — the only thing that clicks
$A2  = $C['accent_2'];    // sage — quiet support
$TINT = $C['hero_bg'];    // blush surface for the calm sections
$INK  = $C['ink'];
$BG   = $C['page_bg'];
$CAL  = 'https://calendly.com/lukegouldenpt/coachingcall?utm_source=lukegouldencoaching&utm_medium=landing_page&utm_campaign=lgc_lp&utm_content=';
?>
<style>
  :root{
    --a:<?= $A ?>; --a2:<?= $A2 ?>; --tint:<?= $TINT ?>; --ink:<?= $INK ?>; --bg:<?= $BG ?>;
    --soft:<?= lp_alpha($INK, .62) ?>;
    --line:<?= lp_alpha($INK, .12) ?>;
    --card:<?= lp_shade($BG, 3) ?>;
    --a-soft:<?= lp_alpha($A, .1) ?>;
    --a2-soft:<?= lp_alpha($A2, .16) ?>;
    --shadow:0 18px 50px <?= lp_alpha($INK, .09) ?>;
    --shadow-sm:0 6px 20px <?= lp_alpha($INK, .06) ?>;
    --r:22px;
  }
  *{box-sizing:border-box}
  html{scroll-behavior:smooth;scroll-padding-top:6rem}
  body{margin:0;background:var(--bg);color:var(--ink);
       font:17px/1.65 Manrope,system-ui,-apple-system,"Segoe UI",sans-serif;
       -webkit-font-smoothing:antialiased}
  section[id]{scroll-margin-top:6rem}
  .wrap{max-width:72rem;margin:0 auto;padding-inline:1.5rem}
  h1,h2,h3{letter-spacing:-.025em;line-height:1.12;margin:0}
  h1{font-size:clamp(2.4rem,5.2vw,3.9rem);font-weight:800}
  h2{font-size:clamp(1.8rem,3.4vw,2.6rem);font-weight:800}
  h3{font-size:1.12rem;font-weight:800;letter-spacing:-.01em}
  p{margin:0}
  a{color:inherit}

  .eyebrow{display:inline-flex;align-items:center;gap:.45rem;background:var(--a-soft);color:var(--a);
           font-size:.7rem;font-weight:800;letter-spacing:.11em;text-transform:uppercase;
           padding:.45rem .85rem;border-radius:99px}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
       background:var(--a);color:#fff;text-decoration:none;border:0;cursor:pointer;
       font:inherit;font-weight:800;font-size:.82rem;letter-spacing:.06em;text-transform:uppercase;
       padding:1.05rem 1.9rem;border-radius:99px;box-shadow:var(--shadow-sm);
       transition:transform .16s ease, box-shadow .16s ease}
  .btn:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
  .btn--ghost{background:transparent;color:var(--ink);border:1.5px solid var(--line);box-shadow:none}
  .trust{display:flex;flex-wrap:wrap;gap:.35rem 1.1rem;margin-top:1rem;
         font-size:.84rem;color:var(--soft)}
  .trust span{display:inline-flex;align-items:center;gap:.4rem;white-space:nowrap}
  .trust svg{width:1rem;height:1rem;color:var(--a2);flex:none}

  /* ---- Header ---- */
  header.top{position:sticky;top:0;z-index:50;background:<?= lp_alpha($BG, .82) ?>;
             backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid transparent;
             transition:border-color .2s}
  header.top.stuck{border-bottom-color:var(--line)}
  .nav{display:flex;align-items:center;justify-content:space-between;gap:1.5rem;min-height:4.6rem}
  .brand{display:inline-flex;align-items:center;gap:.6rem;color:var(--ink);text-decoration:none}
  .brand svg{height:1.35rem;width:auto}
  .brand span{font-size:.76rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase}
  .navlinks{display:flex;gap:2rem;list-style:none;margin:0;padding:0}
  .navlinks a{font-size:.74rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
              text-decoration:none;color:var(--soft)}
  .navlinks a:hover{color:var(--a)}
  .nav .btn{padding:.7rem 1.2rem;font-size:.68rem}
  @media(max-width:60em){ .navlinks{display:none} }

  /* ---- Hero ---- */
  .hero{position:relative;overflow:hidden;padding-block:clamp(3rem,7vw,6rem) clamp(3rem,6vw,5rem)}
  .hero::before,.hero::after{content:"";position:absolute;border-radius:50%;filter:blur(60px);z-index:0}
  .hero::before{width:34rem;height:34rem;background:var(--a-soft);top:-12rem;right:-10rem}
  .hero::after{width:26rem;height:26rem;background:var(--a2-soft);bottom:-14rem;left:-8rem}
  .hero .wrap{position:relative;z-index:1}
  .hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:clamp(2rem,5vw,4rem);align-items:center}
  .hero h1{margin:1.25rem 0 0}
  .hero h1 em{display:block;font-style:normal;font-size:.44em;font-weight:600;color:var(--a);
              letter-spacing:-.01em;margin-top:.9rem;line-height:1.35}
  .hero .lede{font-size:1.08rem;color:var(--soft);max-width:34ch;margin:1.4rem 0 2rem}
  .hero .cta-row{display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
  .hero .note{font-size:.84rem;color:var(--soft)}

  /* Portrait: framed, never covered. The proof chips sit outside the frame,
     below the face — the last version put a card across Luke's face and that is
     the single strongest trust signal on the page. */
  .shot{position:relative}
  .shot .frame{border-radius:calc(var(--r) + 8px);overflow:hidden;box-shadow:var(--shadow);
               aspect-ratio:4/5;background:var(--tint)}
  .shot .frame img{width:100%;height:100%;object-fit:cover;object-position:center 18%;display:block}
  .chips{display:flex;gap:.75rem;margin-top:-2.2rem;padding-left:1.2rem;position:relative;z-index:2;flex-wrap:wrap}
  .chip{background:<?= lp_shade($BG, 6) ?>;border:1px solid var(--line);border-radius:16px;
        padding:.7rem 1rem;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:.6rem}
  .chip svg{width:1.15rem;height:1.15rem;color:var(--a2);flex:none}
  .chip b{display:block;font-size:1rem;font-weight:800;line-height:1.1}
  .chip span{font-size:.68rem;color:var(--soft);letter-spacing:.04em}

  .rating{display:flex;align-items:center;gap:.6rem;margin-top:1.6rem;font-size:.86rem;color:var(--soft);flex-wrap:wrap}
  .rating .stars{color:#00B67A;letter-spacing:.16em}
  .rating b{color:var(--ink)}

  /* ---- Sections ---- */
  .sec{padding-block:clamp(3.5rem,7vw,6rem)}
  .sec--tint{background:var(--tint)}
  .sec--card{background:var(--card)}
  .head{max-width:38rem;margin-bottom:clamp(2rem,4vw,3rem)}
  .head.center{margin-inline:auto;text-align:center}
  .head p{color:var(--soft);margin-top:.9rem;font-size:1.02rem}

  .grid{display:grid;gap:1.25rem}
  .g2{grid-template-columns:repeat(2,1fr)}
  .g3{grid-template-columns:repeat(3,1fr)}
  .g4{grid-template-columns:repeat(4,1fr)}

  .card{background:<?= lp_shade($BG, 5) ?>;border:1px solid var(--line);border-radius:var(--r);
        padding:1.6rem;box-shadow:var(--shadow-sm)}
  .card svg.ic{width:1.9rem;height:1.9rem;color:var(--a);margin-bottom:.9rem}
  .card p{color:var(--soft);font-size:.94rem;margin-top:.5rem}

  /* Familiar list — plain, honest, no icons shouting */
  .familiar{display:grid;grid-template-columns:1fr 1fr;gap:.9rem 2rem}
  .familiar li{list-style:none;display:flex;gap:.7rem;align-items:flex-start;color:var(--soft)}
  .familiar svg{width:1.2rem;height:1.2rem;color:var(--a);flex:none;margin-top:.28rem}
  .familiar-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:clamp(2rem,4vw,3.5rem);align-items:center}
  .familiar-img{border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow);aspect-ratio:4/3}
  .familiar-img img{width:100%;height:100%;object-fit:cover;display:block}

  /* Results */
  .res{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem}
  .res figure{margin:0;border-radius:var(--r);overflow:hidden;background:<?= lp_shade($BG, 5) ?>;
              border:1px solid var(--line);box-shadow:var(--shadow-sm)}
  .res img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block}
  .res figcaption{padding:1rem 1.1rem;display:flex;align-items:baseline;gap:.6rem;flex-wrap:wrap}
  .res .nm{font-weight:800}
  .res .rs{color:var(--a);font-weight:800}
  .res .wk{font-size:.78rem;color:var(--soft);margin-left:auto}

  .quotes{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-top:1.25rem}
  .quote{background:<?= lp_shade($BG, 5) ?>;border:1px solid var(--line);border-radius:var(--r);padding:1.5rem}
  .quote p{font-size:.96rem;line-height:1.6}
  .quote .who{margin-top:1rem;font-size:.8rem;color:var(--soft);font-weight:700}
  .quote .stars{color:#00B67A;letter-spacing:.14em;font-size:.8rem;display:block;margin-bottom:.7rem}

  /* Steps */
  .steps{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;counter-reset:s}
  .step{position:relative;padding-top:2.6rem}
  .step::before{counter-increment:s;content:counter(s);position:absolute;top:0;left:0;
                width:2.1rem;height:2.1rem;border-radius:99px;background:var(--a);color:#fff;
                display:grid;place-items:center;font-size:.82rem;font-weight:800}
  .step p{color:var(--soft);font-size:.92rem;margin-top:.45rem}

  /* Included */
  .inc{display:grid;grid-template-columns:1fr 1fr;gap:.85rem 2rem;list-style:none;margin:0;padding:0}
  .inc li{display:flex;gap:.65rem;align-items:flex-start}
  .inc svg{width:1.25rem;height:1.25rem;color:var(--a2);flex:none;margin-top:.22rem}

  /* Video */
  .video{max-width:56rem;margin-inline:auto;border-radius:var(--r);overflow:hidden;
         box-shadow:var(--shadow);position:relative;padding-top:56.25%}
  .video iframe{position:absolute;inset:0;width:100%;height:100%;border:0}

  /* Band + closing */
  .band{background:var(--tint);text-align:center}
  .band .wrap{padding-block:clamp(3rem,6vw,4.5rem);display:grid;justify-items:center;gap:1.2rem}
  .band h2{max-width:22ch}
  .band h2 em{font-style:normal;color:var(--a)}
  .band p{color:var(--soft);max-width:52ch}

  .closing{display:grid;grid-template-columns:1fr 1fr;gap:clamp(2rem,4vw,4rem);align-items:center}
  .closing-img{border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow);aspect-ratio:4/3}
  .closing-img img{width:100%;height:100%;object-fit:cover;display:block}
  .closing h2 em{font-style:normal;display:block;color:var(--a)}
  .closing p{color:var(--soft);margin:1rem 0 1.75rem}

  /* FAQ */
  .faq{max-width:44rem;margin-inline:auto}
  .faq details{border-bottom:1px solid var(--line)}
  .faq summary{list-style:none;cursor:pointer;padding:1.15rem 0;font-weight:700;
               display:flex;align-items:center;justify-content:space-between;gap:1rem}
  .faq summary::-webkit-details-marker{display:none}
  .faq summary::after{content:"+";color:var(--a);font-weight:800;font-size:1.2rem}
  .faq details[open] summary::after{content:"–"}
  .faq .a{padding:0 0 1.15rem;color:var(--soft);font-size:.96rem}

  footer.foot{border-top:1px solid var(--line);padding-block:2rem;font-size:.8rem;color:var(--soft)}
  .foot .wrap{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:center}

  /* Sticky mobile CTA — this traffic is overwhelmingly phones. */
  .sticky{position:fixed;left:0;right:0;bottom:0;z-index:60;display:none;align-items:center;gap:1rem;
          padding:.7rem 1rem calc(.7rem + env(safe-area-inset-bottom,0px));
          background:<?= lp_alpha($BG, .95) ?>;backdrop-filter:blur(12px);border-top:1px solid var(--line);
          transform:translateY(110%);transition:transform .28s cubic-bezier(.4,0,.2,1)}
  .sticky.on{transform:translateY(0)}
  .sticky .t{flex:1;line-height:1.25}
  .sticky .t b{display:block;font-size:.9rem}
  .sticky .t span{font-size:.72rem;color:var(--soft)}
  .sticky .btn{padding:.75rem 1.1rem;font-size:.68rem}
  @media(max-width:60em){ .sticky{display:flex} }

  @media(max-width:62em){
    .hero-grid,.familiar-grid,.closing{grid-template-columns:1fr}
    .shot{order:-1}
    .g4,.steps{grid-template-columns:1fr 1fr}
    .g3,.res,.quotes{grid-template-columns:1fr 1fr}
  }
  @media(max-width:40em){
    .g2,.g3,.g4,.steps,.res,.quotes,.familiar,.inc{grid-template-columns:1fr}
  }
  @media(prefers-reduced-motion:reduce){ *{transition:none!important;scroll-behavior:auto} }
</style>
</head>
<body>

<header class="top" id="top">
  <div class="wrap nav">
    <a class="brand" href="#top">
      <svg viewBox="76 78 298 237" fill="currentColor" aria-hidden="true">
        <path d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
        <rect x="171" y="179" width="203" height="36"/>
      </svg>
      <span>Luke Goulden</span>
    </a>
    <ul class="navlinks">
      <li><a href="#familiar">Coaching</a></li>
      <li><a href="#results">Results</a></li>
      <li><a href="#how">How it works</a></li>
      <li><a href="#faq">FAQ</a></li>
    </ul>
    <a class="btn" href="<?= $CAL ?>header" target="_blank" rel="noopener"><?= htmlspecialchars($T['cta_label_short']) ?></a>
  </div>
</header>

<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <span class="eyebrow"><?= $T['eyebrow'] ?></span>
      <h1><?= $T['headline'] ?><em><?= $T['headline_accent'] ?></em></h1>
      <p class="lede"><?= $T['sub'] ?></p>

      <div class="cta-row">
        <a class="btn" href="<?= $CAL ?>hero" target="_blank" rel="noopener"><?= htmlspecialchars($T['cta_label']) ?></a>
        <span class="note"><?= htmlspecialchars($T['cta_note']) ?></span>
      </div>

      <p class="trust">
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Pick a time that suits you</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Free — no obligation</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Speak directly with Luke</span>
      </p>

      <div class="rating">
        <span class="stars" aria-hidden="true">★★★★★</span>
        <span><b>Excellent</b> · 4.9 out of 5 from 100+ reviews on Trustpilot</span>
      </div>
    </div>

    <div class="shot">
      <div class="frame">
        <img src="<?= htmlspecialchars($IMG['hero'] ?? '/assets/luke-hero.jpg') ?>" alt="Luke Goulden, online health and fitness coach">
      </div>
      <div class="chips">
        <div class="chip">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-1a7 7 0 0114 0v1M16 3.5a4 4 0 010 7M22 21v-1a7 7 0 00-4-6.3"/></svg>
          <span><b>100+</b><span>Clients coached</span></span>
        </div>
        <div class="chip">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.6 5.6 6 .8-4.4 4.2 1.1 6.1L12 16.9 6.7 19.7l1.1-6.1L3.4 9.4l6-.8z"/></svg>
          <span><b>4.9 / 5</b><span>100+ reviews</span></span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($VID !== ''): ?>
<section class="sec sec--card" data-screen-label="Video">
  <div class="wrap">
    <div class="head center">
      <h2>Two minutes with Luke</h2>
      <p>What coaching actually looks like, in his words.</p>
    </div>
    <div class="video">
      <iframe src="<?= htmlspecialchars($VID) ?>" title="Luke explains how coaching works"
              allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="sec" id="familiar" data-screen-label="Sound familiar">
  <div class="wrap familiar-grid">
    <div>
      <span class="eyebrow">Where you are now</span>
      <h2 style="margin-top:1rem">Does any of this sound familiar?</h2>
      <ul class="familiar" style="margin:1.75rem 0 0;padding:0">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg><span>You’ve tried every plan — nothing sticks past week three</span></li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg><span>You’re tired in a way sleep doesn’t fix</span></li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg><span>Everyone else’s needs come first, every time</span></li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg><span>You know what to do. Doing it consistently is the hard part</span></li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg><span>Food has become something to feel guilty about</span></li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg><span>You’d like to feel like yourself again</span></li>
      </ul>
    </div>
    <div class="familiar-img">
      <img src="<?= htmlspecialchars($IMG['lifestyle'] ?? '/assets/lifestyle.jpg') ?>" alt="Training that fits around a real week" loading="lazy">
    </div>
  </div>
</section>

<section class="sec sec--card" data-screen-label="What changes">
  <div class="wrap">
    <div class="head center">
      <h2>What actually changes</h2>
      <p>Not a diet. A way of eating, training and living that survives a bad week.</p>
    </div>
    <div class="grid g4">
      <div class="card">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/></svg>
        <h3>A plan that fits your week</h3>
        <p>Three or four sessions, 30–60 minutes, built around the time you actually have — not the time a programme assumes you have.</p>
      </div>
      <div class="card">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11h18M5 11v3a7 7 0 0014 0v-3M8 11V7a2 2 0 012-2h4a2 2 0 012 2v4"/></svg>
        <h3>Food without the guilt</h3>
        <p>Meals out, a glass of wine, the school run. Nutrition that bends around real life instead of breaking because of it.</p>
      </div>
      <div class="card">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L4.5 13.5H11L9.5 22 19 10.5h-6.5L13 2z"/></svg>
        <h3>Energy you can feel</h3>
        <p>Most clients notice this first — before the scale moves, before the photos. Strength, sleep, and getting through the afternoon.</p>
      </div>
      <div class="card">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
        <h3>Someone in your corner</h3>
        <p>Weekly check-ins with Luke. He adjusts the plan when life moves, and he notices when you go quiet.</p>
      </div>
    </div>
  </div>
</section>

<section class="sec" id="results" data-screen-label="Results">
  <div class="wrap">
    <div class="head">
      <span class="eyebrow">Client proof</span>
      <h2 style="margin-top:1rem">Real people. Real results.</h2>
      <p>Ordinary weeks, ordinary lives. No 5am ice baths.</p>
    </div>

    <div class="res">
      <?php foreach ($RES as $r): ?>
        <figure>
          <img src="<?= htmlspecialchars($r['img']) ?>" alt="<?= htmlspecialchars($r['name']) ?> — before and after" loading="lazy">
          <figcaption>
            <span class="nm"><?= htmlspecialchars($r['name']) ?></span>
            <span class="rs"><?= htmlspecialchars($r['result']) ?></span>
            <span class="wk"><?= htmlspecialchars($r['weeks']) ?></span>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>

    <div class="quotes">
      <div class="quote">
        <span class="stars" aria-hidden="true">★★★★★</span>
        <p>“I’ve done every diet going. This is the first time I haven’t felt like I was white-knuckling it — and the first time it’s still here months later.”</p>
        <div class="who">Helen · 12 weeks</div>
      </div>
      <div class="quote">
        <span class="stars" aria-hidden="true">★★★★★</span>
        <p>“Luke worked around my shifts and my kids instead of telling me to find more time. That’s the whole difference.”</p>
        <div class="who">Verified review · Trustpilot</div>
      </div>
      <div class="quote">
        <span class="stars" aria-hidden="true">★★★★★</span>
        <p>“The check-ins are the bit that works. Knowing someone is actually looking changed how I show up.”</p>
        <div class="who">Verified review · Google</div>
      </div>
    </div>
  </div>
</section>

<section class="band" data-screen-label="Mid-page CTA">
  <div class="wrap">
    <h2><?= $T['band_title'] ?><em><?= $T['band_accent'] ?></em></h2>
    <p><?= $T['band_body'] ?></p>
    <a class="btn" href="<?= $CAL ?>after_results" target="_blank" rel="noopener"><?= htmlspecialchars($T['cta_label']) ?></a>
    <p class="trust" style="justify-content:center">
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Pick a time that suits you</span>
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Free — no obligation</span>
    </p>
  </div>
</section>

<section class="sec" id="how" data-screen-label="How it works">
  <div class="wrap">
    <div class="head center">
      <h2>How coaching works</h2>
      <p>Four steps. The first one is a conversation, not a commitment.</p>
    </div>
    <div class="steps">
      <div class="step">
        <h3>Game plan call</h3>
        <p>Thirty minutes. Where you are, what you want, what has gone wrong before.</p>
      </div>
      <div class="step">
        <h3>Your plan</h3>
        <p>Training, food and habits, built around your week — not a template.</p>
      </div>
      <div class="step">
        <h3>Weekly check-ins</h3>
        <p>Luke reviews, adjusts and keeps you honest. Life moves; the plan moves with it.</p>
      </div>
      <div class="step">
        <h3>It sticks</h3>
        <p>You end up with habits you keep — not a result you have to defend.</p>
      </div>
    </div>
  </div>
</section>

<section class="sec sec--tint" data-screen-label="What's included">
  <div class="wrap">
    <div class="head">
      <h2>What’s included</h2>
      <p>Everything, for one monthly fee. No upsells.</p>
    </div>
    <ul class="inc">
      <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>Personalised training programme</li>
      <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>Nutrition guidance that fits your life</li>
      <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>Weekly check-ins with Luke</li>
      <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>Habit coaching, not willpower</li>
      <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>Coaching app with your plan and progress</li>
      <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>Direct message access when you need him</li>
    </ul>
    <div style="margin-top:2.5rem;display:grid;justify-items:start;gap:.75rem">
      <a class="btn" href="<?= $CAL ?>after_included" target="_blank" rel="noopener"><?= htmlspecialchars($T['cta_label']) ?></a>
      <span class="note" style="font-size:.84rem;color:var(--soft)"><?= htmlspecialchars($T['cta_note']) ?></span>
    </div>
  </div>
</section>

<section class="sec" id="faq" data-screen-label="FAQ">
  <div class="wrap">
    <div class="head center"><h2>Questions people ask first</h2></div>
    <div class="faq">
      <details>
        <summary>I’m not fit enough to start.</summary>
        <div class="a">Everyone starts somewhere, and the plan is written for where you are — not where a programme assumes you should be. Week one is meant to feel doable.</div>
      </details>
      <details>
        <summary>How much time do I actually need?</summary>
        <div class="a">Most clients train three or four times a week, 30–60 minutes. If that’s not realistic right now, say so on the call and Luke will build around what is.</div>
      </details>
      <details>
        <summary>Do I need a gym?</summary>
        <div class="a">No. Full gym, home setup, or nothing but your body weight — the programme adapts to the equipment you have.</div>
      </details>
      <details>
        <summary>Will I have to give up food I like?</summary>
        <div class="a">No. A plan you resent is a plan you quit. Meals out, chocolate, wine — it all fits, and knowing how is most of the work.</div>
      </details>
      <details>
        <summary>What if I fall off?</summary>
        <div class="a">You will, at some point. That’s the week the check-in earns its keep — you get a reset, not a lecture.</div>
      </details>
      <details>
        <summary>What happens on the call?</summary>
        <div class="a">Thirty minutes with Luke. He asks what you’ve tried, what your week looks like and what you want. If coaching isn’t right for you, he’ll say so.</div>
      </details>
    </div>
  </div>
</section>

<section class="sec sec--card" data-screen-label="Closing CTA">
  <div class="wrap closing">
    <div class="closing-img">
      <img src="<?= htmlspecialchars($IMG['closing'] ?? '/assets/lifestyle.jpg') ?>" alt="" loading="lazy">
    </div>
    <div>
      <span class="eyebrow">Start where you are</span>
      <h2 style="margin-top:1rem"><?= $T['closing_title'] ?><em><?= $T['closing_accent'] ?></em></h2>
      <p><?= $T['closing_body'] ?></p>
      <a class="btn" href="<?= $CAL ?>closing" target="_blank" rel="noopener"><?= htmlspecialchars($T['cta_label']) ?></a>
      <p class="trust">
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Free — no obligation</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Speak directly with Luke</span>
      </p>
    </div>
  </div>
</section>

<footer class="foot">
  <div class="wrap">
    <a class="brand" href="#top">
      <svg viewBox="76 78 298 237" fill="currentColor" aria-hidden="true">
        <path d="M305.5 155A118.5 118.5 0 1 0 305.5 238L264.6 238A81.5 81.5 0 1 1 264.6 155Z"/>
        <rect x="171" y="179" width="203" height="36"/>
      </svg>
      <span>Luke Goulden</span>
    </a>
    <div>© <?= gmdate('Y') ?> Luke Goulden Coaching. All rights reserved.</div>
  </div>
</footer>

<div class="sticky" id="sticky" aria-hidden="true">
  <div class="t">
    <b>Ready when you are</b>
    <span>Free call · pick your time</span>
  </div>
  <a class="btn" href="<?= $CAL ?>sticky_mobile" target="_blank" rel="noopener"><?= htmlspecialchars($T['cta_label_short']) ?></a>
</div>

<script>
(function () {
  /* Header hairline appears only once you've scrolled — keeps the top of the
     page as open as possible, which is most of what "light" means here. */
  var top = document.querySelector('header.top');
  addEventListener('scroll', function () {
    top.classList.toggle('stuck', scrollY > 8);
  }, { passive: true });

  /* Sticky CTA appears once the hero button is gone, never alongside it. */
  var sticky = document.getElementById('sticky'),
      hero   = document.querySelector('.hero');
  if (sticky && hero && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (e) {
      var visible = e[0].isIntersecting;
      sticky.classList.toggle('on', !visible);
      sticky.setAttribute('aria-hidden', visible ? 'true' : 'false');
    }, { rootMargin: '-45% 0px 0px 0px' }).observe(hero);
  }

<?php if (!isset($_GET['preview'])): /* Thumbnails in /admin.php load this page in
    an iframe. Without this guard, opening the admin would log a page view on
    every card and quietly corrupt the numbers the admin exists to show. */ ?>
  /* First-party, cookieless measurement — no cookies, no IPs, no consent banner. */
  function beacon(params) {
    try {
      var url = '/track.php?' + params + '&t=' + Date.now();
      if (navigator.sendBeacon) { navigator.sendBeacon(url); }
      else { (new Image()).src = url; }
    } catch (err) { /* tracking must never break the page */ }
  }

  var campaign = '';
  try { campaign = new URLSearchParams(location.search).get('utm_source') || ''; } catch (err) {}

  beacon('e=view&lp=<?= $LP_ID ?>&r=' + encodeURIComponent(document.referrer || '') +
         '&s=' + encodeURIComponent(campaign));

  document.querySelectorAll('a[href*="calendly.com"]').forEach(function (link) {
    link.addEventListener('click', function () {
      var id = '';
      try { id = new URL(link.href).searchParams.get('utm_content') || ''; } catch (err) {}
      if (id) { beacon('e=cta&lp=<?= $LP_ID ?>&c=' + encodeURIComponent(id)); }
    });
  });
<?php endif; ?>
})();
</script>
</body>
</html>
