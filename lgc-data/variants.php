<?php
/**
 * Landing page variants — the single source of truth for every version of the page.
 *
 * A variant is: a template + colours + copy + images + a live switch. Nothing is
 * hard-coded in the pages themselves, so anything here can be changed in
 * /admin.php without touching code.
 *
 *   template : 'classic' → the dark/editorial build (lp-body.php)
 *              'light'   → the light, modern build (lp-body-light.php)
 *   images   : every photo on the page. Swap any of them per page in the admin.
 *   results  : the before/after grid — image, name, result, timeframe.
 *   video    : optional. The video block only exists when there is a URL.
 *   live     : false → the page 404s for the public. Signed-in users still see
 *              it, so a campaign page can be built and reviewed before it runs.
 */
return [

  '01' => [
    'name' => 'Original — G-Cut teal',
    'note' => 'The calm, editorial original. Safe default.',
    'live' => true,
    'path' => '/',
    'template' => 'classic',
    'colors' => [
      'hero_bg'   => '#1A3C34',   // hero / dark sections
      'hero_text' => '#F7F5F0',   // text on those dark sections
      'accent'    => '#E05A3A',   // CTA buttons — the only accent that clicks
      'accent_2'  => '#84B59F',   // secondary accent (sub-headline, ticks)
      'page_bg'   => '#F7F5F0',   // page background
      'ink'       => '#1E1E1E',   // body text
    ],
    'video'  => '',
    'images' => [
      'hero'      => '/assets/luke-hero.jpg',
      'lifestyle' => '/assets/lifestyle.jpg',
      'closing'   => '/assets/luke-hero.jpg',
    ],
    'results' => [
      ['img' => '/assets/tf-craig.jpg',  'name' => 'Craig',  'result' => '−16.6kg', 'weeks' => '12 weeks'],
      ['img' => '/assets/tf-helen.jpg',  'name' => 'Helen',  'result' => '−11kg',   'weeks' => '12 weeks'],
      ['img' => '/assets/tf-jono.jpg',   'name' => 'Jono',   'result' => '−50lbs+', 'weeks' => '6 months'],
      ['img' => '/assets/tf-scott.jpg',  'name' => 'Scott',  'result' => '−45lbs',  'weeks' => '5 months'],
      ['img' => '/assets/tf-richie.jpg', 'name' => 'Richie', 'result' => '−14lbs',  'weeks' => '8 weeks, before his wedding'],
    ],
    'text' => [
      'eyebrow'         => 'For busy parents &amp; professionals over 30',
      'headline'        => 'Leaner. Stronger. More&nbsp;confident.',
      'headline_accent' => '— without living in the gym.',
      'sub'             => 'Personalised coaching, accountability and support to build results that actually last.',
      'cta_label'       => 'Book your free discovery call',
      'cta_label_short' => 'Book free call',
      'cta_note'        => 'Spots are limited — book yours today.',
      'band_title'      => 'Craig, Helen and Jono all started the same way.',
      'band_accent'     => ' With one call.',
      'band_body'       => 'Pick a slot in Luke’s calendar. Tell him where you are now and what you want to change, and he’ll map out exactly how coaching would work around your week — before you commit to anything.',
      'closing_title'   => 'Stop starting over.',
      'closing_accent'  => 'You deserve better.',
      'closing_body'    => 'You don’t need another diet. You need a plan, support and accountability.',
    ],
  ],

  '02' => [
    'name' => 'Dark — gold',
    'note' => 'High-contrast dark. Built for cold paid traffic.',
    'live' => true,
    'path' => '/_02/',
    'template' => 'classic',
    'colors' => [
      'hero_bg'   => '#141414',
      'hero_text' => '#F5F3EF',
      'accent'    => '#E8A838',
      'accent_2'  => '#F1C266',
      'page_bg'   => '#1D1D1D',
      'ink'       => '#E8E6E1',
    ],
    'video'  => '',
    'images' => [
      'hero'      => '/assets/luke-hero.jpg',
      'lifestyle' => '/assets/lifestyle.jpg',
      'closing'   => '/assets/luke-hero.jpg',
    ],
    'results' => [
      ['img' => '/assets/tf-craig.jpg',  'name' => 'Craig',  'result' => '−16.6kg', 'weeks' => '12 weeks'],
      ['img' => '/assets/tf-helen.jpg',  'name' => 'Helen',  'result' => '−11kg',   'weeks' => '12 weeks'],
      ['img' => '/assets/tf-jono.jpg',   'name' => 'Jono',   'result' => '−50lbs+', 'weeks' => '6 months'],
      ['img' => '/assets/tf-scott.jpg',  'name' => 'Scott',  'result' => '−45lbs',  'weeks' => '5 months'],
      ['img' => '/assets/tf-richie.jpg', 'name' => 'Richie', 'result' => '−14lbs',  'weeks' => '8 weeks, before his wedding'],
    ],
    'text' => [
      'eyebrow'         => 'Busy professionals &amp; parents',
      'headline'        => 'Get leaner, stronger &amp; more&nbsp;confident',
      'headline_accent' => 'without living in the gym.',
      'sub'             => 'Personalised coaching, accountability and support to help you build sustainable results that actually last.',
      'cta_label'       => 'Apply for coaching',
      'cta_label_short' => 'Apply now',
      'cta_note'        => 'Spaces are limited — apply today.',
      'band_title'      => 'Craig, Helen and Jono all started the same way.',
      'band_accent'     => ' With one call.',
      'band_body'       => 'Pick a slot in Luke’s calendar. Tell him where you are now and what you want to change, and he’ll map out exactly how coaching would work around your week — before you commit to anything.',
      'closing_title'   => 'Stop starting over.',
      'closing_accent'  => 'You deserve better.',
      'closing_body'    => 'You don’t need another diet. You need a plan, support and accountability.',
    ],
  ],

  '03' => [
    'name' => 'Light — gold',
    'note' => 'Warm and editorial. Better for retargeting warm audiences.',
    'live' => true,
    'path' => '/_03/',
    'template' => 'classic',
    'colors' => [
      'hero_bg'   => '#141414',
      'hero_text' => '#F5F3EF',
      'accent'    => '#E8A838',
      'accent_2'  => '#CF9523',
      'page_bg'   => '#F5F3EF',
      'ink'       => '#1E1E1E',
    ],
    'video'  => '',
    'images' => [
      'hero'      => '/assets/luke-hero.jpg',
      'lifestyle' => '/assets/lifestyle.jpg',
      'closing'   => '/assets/luke-hero.jpg',
    ],
    'results' => [
      ['img' => '/assets/tf-craig.jpg',  'name' => 'Craig',  'result' => '−16.6kg', 'weeks' => '12 weeks'],
      ['img' => '/assets/tf-helen.jpg',  'name' => 'Helen',  'result' => '−11kg',   'weeks' => '12 weeks'],
      ['img' => '/assets/tf-jono.jpg',   'name' => 'Jono',   'result' => '−50lbs+', 'weeks' => '6 months'],
      ['img' => '/assets/tf-scott.jpg',  'name' => 'Scott',  'result' => '−45lbs',  'weeks' => '5 months'],
      ['img' => '/assets/tf-richie.jpg', 'name' => 'Richie', 'result' => '−14lbs',  'weeks' => '8 weeks, before his wedding'],
    ],
    'text' => [
      'eyebrow'         => 'Busy professionals &amp; parents',
      'headline'        => 'Get leaner, stronger &amp; more&nbsp;confident',
      'headline_accent' => 'without living in the gym.',
      'sub'             => 'Personalised coaching, accountability and support to help you build sustainable results that actually last.',
      'cta_label'       => 'Apply for coaching',
      'cta_label_short' => 'Apply now',
      'cta_note'        => 'Spaces are limited — apply today.',
      'band_title'      => 'Craig, Helen and Jono all started the same way.',
      'band_accent'     => ' With one call.',
      'band_body'       => 'Pick a slot in Luke’s calendar. Tell him where you are now and what you want to change, and he’ll map out exactly how coaching would work around your week — before you commit to anything.',
      'closing_title'   => 'Stop starting over.',
      'closing_accent'  => 'You deserve better.',
      'closing_body'    => 'You don’t need another diet. You need a plan, support and accountability.',
    ],
  ],

  '04' => [
    'name' => 'Project You — blue',
    'note' => 'Performance / body-composition angle. Male-skewed audiences.',
    'live' => false,
    'path' => '/_04/',
    'template' => 'classic',
    'colors' => [
      'hero_bg'   => '#0E1420',
      'hero_text' => '#F2F5F9',
      'accent'    => '#2E7FE0',
      'accent_2'  => '#7AA9F7',
      'page_bg'   => '#F4F6F9',
      'ink'       => '#141A24',
    ],
    'video'  => '',
    'images' => [
      'hero'      => '/assets/luke-hero.jpg',
      'lifestyle' => '/assets/lifestyle.jpg',
      'closing'   => '/assets/luke-hero.jpg',
    ],
    'results' => [
      ['img' => '/assets/tf-craig.jpg',  'name' => 'Craig',  'result' => '−16.6kg', 'weeks' => '12 weeks'],
      ['img' => '/assets/tf-helen.jpg',  'name' => 'Helen',  'result' => '−11kg',   'weeks' => '12 weeks'],
      ['img' => '/assets/tf-jono.jpg',   'name' => 'Jono',   'result' => '−50lbs+', 'weeks' => '6 months'],
      ['img' => '/assets/tf-scott.jpg',  'name' => 'Scott',  'result' => '−45lbs',  'weeks' => '5 months'],
      ['img' => '/assets/tf-richie.jpg', 'name' => 'Richie', 'result' => '−14lbs',  'weeks' => '8 weeks, before his wedding'],
    ],
    'text' => [
      'eyebrow'         => 'Project You',
      'headline'        => 'Transform your body. Transform your&nbsp;life.',
      'headline_accent' => 'Coaching for people ready for more.',
      'sub'             => 'Body recomposition coaching for people who want to build muscle, get lean and perform at their best.',
      'cta_label'       => 'Apply for coaching',
      'cta_label_short' => 'Apply now',
      'cta_note'        => 'Spaces are limited — apply today.',
      'band_title'      => 'Craig, Helen and Jono all started the same way.',
      'band_accent'     => ' With one call.',
      'band_body'       => 'Pick a slot in Luke’s calendar. Tell him where you are now and what you want to change, and he’ll map out exactly how coaching would work around your week — before you commit to anything.',
      'closing_title'   => 'Your transformation starts now.',
      'closing_accent'  => 'Stop waiting.',
      'closing_body'    => 'Start building the body and the life you deserve.',
    ],
  ],

  /* --------------------------------------------------------------------------
   * 05 — the light, modern page, written for women.
   *
   * A different template, not just different colours. The dark build sells with
   * intensity; that is the wrong register here. This one is airy and calm, leads
   * with the promise rather than the gym, and puts Helen's result first.
   *
   * Its proof leans on words and ratings as much as photos, because the photo
   * library is still mostly men. Swap any image in the admin as Luke sends more.
   * ------------------------------------------------------------------------ */
  '05' => [
    'name' => 'Strong & Steady — light',
    'note' => 'Modern, light, written for women. Female-targeted campaigns.',
    'live' => false,
    'path' => '/_05/',
    'template' => 'light',
    'colors' => [
      'hero_bg'   => '#F1E4DE',   // soft blush surface (the light build's tinted sections)
      'hero_text' => '#2A2320',   // text on that tint
      'accent'    => '#C4655A',   // terracotta — warm and adult, not pink
      'accent_2'  => '#8AA893',   // muted sage
      'page_bg'   => '#FBF8F5',   // warm off-white page
      'ink'       => '#2A2320',   // body text
    ],
    'video'  => '',   // paste a Vimeo/YouTube embed URL and the video block appears
    'images' => [
      'hero'      => '/assets/luke-hero.jpg',
      'lifestyle' => '/assets/lifestyle.jpg',
      'closing'   => '/assets/lifestyle.jpg',
    ],
    'results' => [
      ['img' => '/assets/tf-helen.jpg', 'name' => 'Helen', 'result' => '−11kg',   'weeks' => '12 weeks'],
      ['img' => '/assets/tf-craig.jpg', 'name' => 'Craig', 'result' => '−16.6kg', 'weeks' => '12 weeks'],
      ['img' => '/assets/tf-scott.jpg', 'name' => 'Scott', 'result' => '−45lbs',  'weeks' => '5 months'],
    ],
    'text' => [
      'eyebrow'         => 'Coaching for women who are done starting over',
      'headline'        => 'Strong, steady, and finally in your&nbsp;corner.',
      'headline_accent' => 'No crash diets. No punishment. No guilt.',
      'sub'             => 'A plan built around your real week — work, family, the lot — with a coach who checks in, adjusts it, and keeps you going on the days motivation doesn’t show up.',
      'cta_label'       => 'Book your free call',
      'cta_label_short' => 'Book free call',
      'cta_note'        => 'Free · 30 minutes · no obligation',
      'band_title'      => 'You already know what to do.',
      'band_accent'     => ' What you need is someone in your corner.',
      'band_body'       => 'One call with Luke. Tell him where you are and what you want to change, and he’ll map out exactly how coaching would work around your week — before you commit to anything.',
      'closing_title'   => 'This is the year it sticks.',
      'closing_accent'  => 'It starts with a conversation.',
      'closing_body'    => 'No pressure and no pitch. Just an honest look at what would actually work for the life you have.',
    ],
  ],

];
