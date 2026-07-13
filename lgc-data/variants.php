<?php
/**
 * Landing page variants — the single source of truth for every version of the page.
 *
 * ONE page template (lp.php) renders all of these. That is deliberate: four
 * copies of the HTML would mean every future fix has to be made four times,
 * and they would drift apart within a week. Here, a variant is just colours +
 * copy + a live switch.
 *
 * Edited through /admin.php — you should not need to touch this file by hand.
 *
 * live = false  → the page 404s for the public. Signed-in users can still
 *                 preview it, so you can build a campaign page before it runs.
 */
return [

  '01' => [
    'name' => 'Original — G-Cut teal',
    'note' => 'The calm, editorial original. Safe default.',
    'live' => true,
    'path' => '/',
    'colors' => [
      'hero_bg'   => '#1A3C34',   // hero / dark sections
      'hero_text' => '#F7F5F0',   // headline on the hero
      'accent'    => '#E05A3A',   // CTA buttons — the only accent that clicks
      'accent_2'  => '#84B59F',   // secondary accent (sub-headline, ticks)
      'page_bg'   => '#F7F5F0',   // page background
      'ink'       => '#1E1E1E',   // body text
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
    'colors' => [
      'hero_bg'   => '#141414',
      'hero_text' => '#F5F3EF',
      'accent'    => '#E8A838',
      'accent_2'  => '#F1C266',
      'page_bg'   => '#1D1D1D',
      'ink'       => '#E8E6E1',
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
    'colors' => [
      'hero_bg'   => '#141414',
      'hero_text' => '#F5F3EF',
      'accent'    => '#E8A838',
      'accent_2'  => '#CF9523',
      'page_bg'   => '#F5F3EF',
      'ink'       => '#1E1E1E',
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
    'colors' => [
      'hero_bg'   => '#0E1420',
      'hero_text' => '#F2F5F9',
      'accent'    => '#2E7FE0',
      'accent_2'  => '#7AA9F7',
      'page_bg'   => '#F4F6F9',
      'ink'       => '#141A24',
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

];
