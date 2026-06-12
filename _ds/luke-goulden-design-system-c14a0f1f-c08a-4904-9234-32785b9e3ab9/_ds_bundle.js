/* @ds-bundle: {"format":3,"namespace":"LukeGouldenDesignSystem_c14a0f","components":[{"name":"GCutMark","sourcePath":"components/brand/Logo.jsx"},{"name":"Logo","sourcePath":"components/brand/Logo.jsx"},{"name":"Button","sourcePath":"components/buttons/Button.jsx"},{"name":"Accordion","sourcePath":"components/content/Accordion.jsx"},{"name":"Card","sourcePath":"components/content/Card.jsx"},{"name":"Testimonial","sourcePath":"components/content/Testimonial.jsx"},{"name":"TickItem","sourcePath":"components/content/TickItem.jsx"},{"name":"Transformation","sourcePath":"components/content/Transformation.jsx"},{"name":"Avatar","sourcePath":"components/data-display/Avatar.jsx"},{"name":"Badge","sourcePath":"components/data-display/Badge.jsx"},{"name":"Stat","sourcePath":"components/data-display/Stat.jsx"},{"name":"Tag","sourcePath":"components/data-display/Tag.jsx"},{"name":"Field","sourcePath":"components/forms/Field.jsx"},{"name":"Input","sourcePath":"components/forms/Input.jsx"},{"name":"Textarea","sourcePath":"components/forms/Textarea.jsx"}],"sourceHashes":{"components/brand/Logo.jsx":"a99406108d4a","components/buttons/Button.jsx":"e5f11c347639","components/content/Accordion.jsx":"859f9b3adbda","components/content/Card.jsx":"a7d3697964af","components/content/Testimonial.jsx":"3ddbb4f1bf3e","components/content/TickItem.jsx":"876c4b3875d3","components/content/Transformation.jsx":"1f8bcaacc2b3","components/data-display/Avatar.jsx":"cf5d33df81cf","components/data-display/Badge.jsx":"3f2f695105c4","components/data-display/Stat.jsx":"f75ad17f82c9","components/data-display/Tag.jsx":"212eb1703062","components/forms/Field.jsx":"8e28055b11f5","components/forms/Input.jsx":"ef616b72f6e4","components/forms/Textarea.jsx":"94d8d5aac203","ui_kits/website/app.jsx":"b2b3315d5ba0","ui_kits/website/icons.jsx":"e0993f91e0eb","ui_kits/website/sections.jsx":"8c5dc6cde0e5"},"inlinedExternals":[],"unexposedExports":[]} */

(() => {

const __ds_ns = (window.LukeGouldenDesignSystem_c14a0f = window.LukeGouldenDesignSystem_c14a0f || {});

const __ds_scope = {};

(__ds_ns.__errors = __ds_ns.__errors || []);

// components/brand/Logo.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const TONES = {
  teal: "var(--lg-teal)",
  white: "var(--lg-offwhite)",
  coral: "var(--lg-coral)",
  black: "var(--lg-charcoal)",
  sage: "var(--lg-sage)"
};

/** The G-Cut mark — pure ring with a crossbar piercing the right side. */
function GCutMark({
  style,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("svg", _extends({
    viewBox: "0 0 300 150",
    fill: "none",
    "aria-hidden": "true",
    style: {
      display: "block",
      height: "1em",
      width: "auto",
      ...style
    }
  }, rest), /*#__PURE__*/React.createElement("circle", {
    cx: "74",
    cy: "75",
    r: "62",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "22"
  }), /*#__PURE__*/React.createElement("rect", {
    x: "74",
    y: "64",
    width: "212",
    height: "22",
    fill: "currentColor"
  }));
}

/**
 * Luke Goulden logo. Renders the G-Cut mark alone, or locked up with the
 * wordmark (horizontal / with-tagline / stacked). Monochrome only.
 */
function Logo({
  variant = "horizontal",
  tone = "teal",
  size = 34,
  tagline = "Real Change · Real Results",
  className = "",
  style,
  ...rest
}) {
  const color = TONES[tone] || TONES.teal;
  const markSize = variant === "mark" ? size : size * 0.86;
  const mark = /*#__PURE__*/React.createElement(GCutMark, {
    style: {
      height: markSize,
      color
    }
  });
  const wordmark = /*#__PURE__*/React.createElement("span", {
    style: {
      fontFamily: "var(--font-display)",
      fontWeight: "var(--fw-medium)",
      textTransform: "uppercase",
      letterSpacing: "var(--tr-wordmark)",
      paddingRight: "0.22em",
      lineHeight: 1.05,
      color,
      display: "block"
    }
  }, /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: size * 0.6,
      display: "block"
    }
  }, "Luke Goulden"), variant === "tagline" && /*#__PURE__*/React.createElement("span", {
    style: {
      fontSize: size * 0.24,
      letterSpacing: "var(--tr-caps)",
      opacity: 0.8,
      display: "block",
      marginTop: size * 0.16
    }
  }, tagline));
  if (variant === "mark") {
    return /*#__PURE__*/React.createElement("span", _extends({
      className: className,
      style: {
        display: "inline-flex",
        color,
        ...style
      }
    }, rest), mark);
  }
  if (variant === "stacked") {
    return /*#__PURE__*/React.createElement("span", _extends({
      className: className,
      style: {
        display: "inline-flex",
        flexDirection: "column",
        alignItems: "center",
        gap: size * 0.34,
        color,
        ...style
      }
    }, rest), mark, /*#__PURE__*/React.createElement("span", {
      style: {
        width: size * 3.2,
        height: 1,
        background: color,
        opacity: 0.4
      }
    }), wordmark);
  }

  // horizontal / tagline
  return /*#__PURE__*/React.createElement("span", _extends({
    className: className,
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: size * 0.5,
      color,
      ...style
    }
  }, rest), mark, /*#__PURE__*/React.createElement("span", {
    style: {
      width: 1,
      alignSelf: "stretch",
      background: color,
      opacity: 0.45
    }
  }), wordmark);
}
Object.assign(__ds_scope, { GCutMark, Logo });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/brand/Logo.jsx", error: String((e && e.message) || e) }); }

// components/buttons/Button.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/**
 * Luke Goulden button. Calm, premium, uppercase tracked label.
 * Renders <a> when `href` is set, otherwise <button>.
 */
function Button({
  variant = "primary",
  size = "md",
  href,
  iconLeft,
  iconRight,
  block = false,
  disabled = false,
  className = "",
  children,
  ...rest
}) {
  const classes = ["lg-btn", `lg-btn--${variant}`, size === "sm" ? "lg-btn--sm" : size === "lg" ? "lg-btn--lg" : "", block ? "lg-btn--block" : "", className].filter(Boolean).join(" ");
  const inner = /*#__PURE__*/React.createElement(React.Fragment, null, iconLeft && /*#__PURE__*/React.createElement("span", {
    className: "lg-btn__icon",
    "aria-hidden": "true"
  }, iconLeft), children && /*#__PURE__*/React.createElement("span", null, children), iconRight && /*#__PURE__*/React.createElement("span", {
    className: "lg-btn__icon",
    "aria-hidden": "true"
  }, iconRight));
  if (href && !disabled) {
    return /*#__PURE__*/React.createElement("a", _extends({
      className: classes,
      href: href
    }, rest), inner);
  }
  return /*#__PURE__*/React.createElement("button", _extends({
    className: classes,
    type: "button",
    disabled: disabled,
    "aria-disabled": disabled || undefined
  }, rest), inner);
}
Object.assign(__ds_scope, { Button });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/buttons/Button.jsx", error: String((e && e.message) || e) }); }

// components/content/Accordion.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** FAQ accordion built on native <details>. Pass items=[{q, a}]. */
function Accordion({
  items = [],
  className = "",
  ...rest
}) {
  return /*#__PURE__*/React.createElement("div", _extends({
    className: ["lg-accordion", className].filter(Boolean).join(" ")
  }, rest), items.map((it, i) => /*#__PURE__*/React.createElement("details", {
    className: "lg-accordion__item",
    key: i,
    open: it.open
  }, /*#__PURE__*/React.createElement("summary", {
    className: "lg-accordion__head"
  }, /*#__PURE__*/React.createElement("span", null, it.q), /*#__PURE__*/React.createElement("svg", {
    className: "lg-accordion__icon",
    width: "18",
    height: "18",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2.2",
    strokeLinecap: "round",
    "aria-hidden": "true"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M12 5v14M5 12h14"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "lg-accordion__body"
  }, it.a))));
}
Object.assign(__ds_scope, { Accordion });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/content/Accordion.jsx", error: String((e && e.message) || e) }); }

// components/content/Card.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Surface container. variant: default | flat | calm | dark. */
function Card({
  variant = "default",
  className = "",
  children,
  ...rest
}) {
  const v = variant === "default" ? "" : `lg-card--${variant}`;
  return /*#__PURE__*/React.createElement("div", _extends({
    className: ["lg-card", v, className].filter(Boolean).join(" ")
  }, rest), children);
}
Object.assign(__ds_scope, { Card });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/content/Card.jsx", error: String((e && e.message) || e) }); }

// components/content/Testimonial.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Testimonial / pull-quote with coral accent rule and optional attribution. */
function Testimonial({
  quote,
  name,
  context,
  avatar,
  className = "",
  children,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("figure", _extends({
    className: ["lg-quote", className].filter(Boolean).join(" "),
    style: {
      margin: 0
    }
  }, rest), /*#__PURE__*/React.createElement("blockquote", {
    className: "lg-quote__text",
    style: {
      margin: 0
    }
  }, quote || children), (name || avatar) && /*#__PURE__*/React.createElement("figcaption", {
    className: "lg-quote__cite"
  }, avatar && /*#__PURE__*/React.createElement("span", {
    className: "lg-avatar",
    style: {
      width: "2.25rem",
      height: "2.25rem",
      fontSize: "0.78rem"
    }
  }, typeof avatar === "string" ? /*#__PURE__*/React.createElement("img", {
    src: avatar,
    alt: name || ""
  }) : avatar), /*#__PURE__*/React.createElement("span", null, name && /*#__PURE__*/React.createElement("b", null, name), name && context && " · ", context && /*#__PURE__*/React.createElement("span", null, context))));
}
Object.assign(__ds_scope, { Testimonial });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/content/Testimonial.jsx", error: String((e && e.message) || e) }); }

// components/content/TickItem.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Feature/exclusion list row with a check (or cross) icon. */
function TickItem({
  variant = "check",
  className = "",
  children,
  ...rest
}) {
  const isCross = variant === "cross";
  return /*#__PURE__*/React.createElement("li", _extends({
    className: ["lg-tick", isCross ? "lg-tick--cross" : "", className].filter(Boolean).join(" "),
    style: {
      listStyle: "none"
    }
  }, rest), /*#__PURE__*/React.createElement("svg", {
    className: "lg-tick__icon",
    width: "20",
    height: "20",
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "2.4",
    strokeLinecap: "round",
    strokeLinejoin: "round",
    "aria-hidden": "true"
  }, /*#__PURE__*/React.createElement("circle", {
    cx: "12",
    cy: "12",
    r: "10"
  }), isCross ? /*#__PURE__*/React.createElement("path", {
    d: "M15 9l-6 6M9 9l6 6"
  }) : /*#__PURE__*/React.createElement("path", {
    d: "M8.5 12.5l2.5 2.5 4.5-5"
  })), /*#__PURE__*/React.createElement("span", null, children));
}
Object.assign(__ds_scope, { TickItem });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/content/TickItem.jsx", error: String((e && e.message) || e) }); }

// components/content/Transformation.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Client result card — photo with overlaid name / result / timeframe. */
function Transformation({
  image,
  name,
  result,
  weeks,
  className = "",
  ...rest
}) {
  return /*#__PURE__*/React.createElement("div", _extends({
    className: ["lg-transform", className].filter(Boolean).join(" ")
  }, rest), image && /*#__PURE__*/React.createElement("img", {
    src: image,
    alt: name ? `${name} — ${result}` : "Client transformation"
  }), /*#__PURE__*/React.createElement("div", {
    className: "lg-transform__cap"
  }, name && /*#__PURE__*/React.createElement("div", {
    className: "lg-transform__name"
  }, name), result && /*#__PURE__*/React.createElement("div", {
    className: "lg-transform__result"
  }, result), weeks && /*#__PURE__*/React.createElement("div", {
    className: "lg-transform__weeks"
  }, weeks)));
}
Object.assign(__ds_scope, { Transformation });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/content/Transformation.jsx", error: String((e && e.message) || e) }); }

// components/data-display/Avatar.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Round avatar — image when `src` given, otherwise initials. */
function Avatar({
  src,
  alt = "",
  initials,
  size = 44,
  className = "",
  style,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("span", _extends({
    className: ["lg-avatar", className].filter(Boolean).join(" "),
    style: {
      width: size,
      height: size,
      fontSize: size * 0.34,
      ...style
    }
  }, rest), src ? /*#__PURE__*/React.createElement("img", {
    src: src,
    alt: alt
  }) : initials);
}
Object.assign(__ds_scope, { Avatar });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data-display/Avatar.jsx", error: String((e && e.message) || e) }); }

// components/data-display/Badge.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Small uppercase status/label chip. */
function Badge({
  tone = "sage",
  className = "",
  children,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("span", _extends({
    className: ["lg-badge", `lg-badge--${tone}`, className].filter(Boolean).join(" ")
  }, rest), children);
}
Object.assign(__ds_scope, { Badge });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data-display/Badge.jsx", error: String((e && e.message) || e) }); }

// components/data-display/Stat.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Headline statistic — big serif-weight number over an uppercase label. */
function Stat({
  value,
  label,
  tone = "default",
  className = "",
  ...rest
}) {
  const toneClass = tone === "coral" ? "lg-stat--coral" : tone === "ondark" ? "lg-stat--ondark" : "";
  return /*#__PURE__*/React.createElement("div", _extends({
    className: ["lg-stat", toneClass, className].filter(Boolean).join(" ")
  }, rest), /*#__PURE__*/React.createElement("span", {
    className: "lg-stat__num"
  }, value), /*#__PURE__*/React.createElement("span", {
    className: "lg-stat__label"
  }, label));
}
Object.assign(__ds_scope, { Stat });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data-display/Stat.jsx", error: String((e && e.message) || e) }); }

// components/data-display/Tag.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Pill tag for categories / filters. */
function Tag({
  filled = false,
  className = "",
  children,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("span", _extends({
    className: ["lg-tag", filled ? "lg-tag--filled" : "", className].filter(Boolean).join(" ")
  }, rest), children);
}
Object.assign(__ds_scope, { Tag });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data-display/Tag.jsx", error: String((e && e.message) || e) }); }

// components/forms/Field.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Form field wrapper — label, control (children), hint/error. */
function Field({
  label,
  hint,
  error,
  htmlFor,
  className = "",
  children,
  ...rest
}) {
  const classes = ["lg-field", error ? "lg-field--error" : "", className].filter(Boolean).join(" ");
  return /*#__PURE__*/React.createElement("div", _extends({
    className: classes
  }, rest), label && /*#__PURE__*/React.createElement("label", {
    className: "lg-label",
    htmlFor: htmlFor
  }, label), children, error ? /*#__PURE__*/React.createElement("span", {
    className: "lg-error"
  }, error) : hint ? /*#__PURE__*/React.createElement("span", {
    className: "lg-hint"
  }, hint) : null);
}
Object.assign(__ds_scope, { Field });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Field.jsx", error: String((e && e.message) || e) }); }

// components/forms/Input.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Text input. Pair inside <Field> for label/hint/error. */
function Input({
  className = "",
  ...rest
}) {
  return /*#__PURE__*/React.createElement("input", _extends({
    className: ["lg-input", className].filter(Boolean).join(" ")
  }, rest));
}
Object.assign(__ds_scope, { Input });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Input.jsx", error: String((e && e.message) || e) }); }

// components/forms/Textarea.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/** Multi-line text input. Pair inside <Field>. Vertical resize only. */
function Textarea({
  className = "",
  rows = 4,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("textarea", _extends({
    className: ["lg-textarea", className].filter(Boolean).join(" "),
    rows: rows
  }, rest));
}
Object.assign(__ds_scope, { Textarea });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Textarea.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/app.jsx
try { (() => {
/* Luke Goulden — website app shell: composition, nav, apply flow. */
const {
  useState
} = React;
const GOALS = ["Lose body fat", "Build strength", "More energy & confidence", "Sustainable habits"];
const TIME = ["2–3 hours / week", "3–4 hours / week", "5+ hours / week"];
function ApplyModal({
  onClose
}) {
  const [step, setStep] = useState(0);
  const [goal, setGoal] = useState(null);
  const [time, setTime] = useState(null);
  const steps = [{
    q: "What's your main goal?",
    opts: GOALS,
    val: goal,
    set: setGoal
  }, {
    q: "How much time can you give?",
    opts: TIME,
    val: time,
    set: setTime
  }];
  const isDone = step === 2;
  const cur = steps[step];
  return /*#__PURE__*/React.createElement("div", {
    className: "wk-modal-scrim",
    onClick: onClose
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-modal",
    onClick: e => e.stopPropagation()
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: "flex",
      justifyContent: "space-between",
      alignItems: "flex-start"
    }
  }, /*#__PURE__*/React.createElement(Lockup, {
    h: 22
  }), /*#__PURE__*/React.createElement("button", {
    className: "lg-btn lg-btn--ghost lg-btn--sm",
    style: {
      minHeight: "auto",
      padding: 6
    },
    onClick: onClose,
    "aria-label": "Close"
  }, /*#__PURE__*/React.createElement(IconClose, {
    size: 18
  }))), !isDone && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: "1.4rem"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "steps-dots"
  }, /*#__PURE__*/React.createElement("span", {
    className: `dot ${step >= 0 ? "on" : ""}`
  }), /*#__PURE__*/React.createElement("span", {
    className: `dot ${step >= 1 ? "on" : ""}`
  })), /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow",
    style: {
      marginBottom: "0.3rem"
    }
  }, "Step ", step + 1, " of 2"), /*#__PURE__*/React.createElement("h3", null, cur.q), /*#__PURE__*/React.createElement("div", {
    className: "opts"
  }, cur.opts.map(o => /*#__PURE__*/React.createElement("button", {
    key: o,
    className: `wk-opt ${cur.val === o ? "sel" : ""}`,
    onClick: () => {
      cur.set(o);
      setTimeout(() => setStep(step + 1), 160);
    }
  }, o)))), isDone && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: "1.6rem",
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "done-ico"
  }, /*#__PURE__*/React.createElement(IconCheckBig, {
    size: 30
  })), /*#__PURE__*/React.createElement("h3", null, "Application received"), /*#__PURE__*/React.createElement("p", {
    style: {
      color: "var(--text-muted)",
      fontSize: "0.95rem"
    }
  }, "Thanks \u2014 we'll review your goal (", goal && goal.toLowerCase(), ") and time, and come back within 48 hours with your next step."), /*#__PURE__*/React.createElement("button", {
    className: "lg-btn lg-btn--primary lg-btn--block",
    style: {
      marginTop: "0.6rem"
    },
    onClick: onClose
  }, "Back to site"))));
}
function App() {
  const [apply, setApply] = useState(false);
  const onNav = (e, id) => {
    e.preventDefault();
    const el = document.getElementById(id);
    if (el) {
      const y = el.getBoundingClientRect().top + window.scrollY - 72;
      window.scrollTo({
        top: y,
        behavior: "smooth"
      });
    }
  };
  const openApply = () => setApply(true);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(Header, {
    onApply: openApply,
    onNav: onNav
  }), /*#__PURE__*/React.createElement(Hero, {
    onApply: openApply,
    onNav: onNav
  }), /*#__PURE__*/React.createElement(Press, null), /*#__PURE__*/React.createElement(Problem, null), /*#__PURE__*/React.createElement(Method, null), /*#__PURE__*/React.createElement(Results, null), /*#__PURE__*/React.createElement(Proof, null), /*#__PURE__*/React.createElement(Included, null), /*#__PURE__*/React.createElement(FAQ, null), /*#__PURE__*/React.createElement(Closing, {
    onApply: openApply
  }), /*#__PURE__*/React.createElement(Footer, null), apply && /*#__PURE__*/React.createElement(ApplyModal, {
    onClose: () => setApply(false)
  }));
}
ReactDOM.createRoot(document.getElementById("root")).render(/*#__PURE__*/React.createElement(App, null));
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/app.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/icons.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/* Luke Goulden — line icons (Lucide-style, 2px stroke, currentColor). */
const Svg = ({
  size = 24,
  sw = 2,
  children,
  ...p
}) => /*#__PURE__*/React.createElement("svg", _extends({
  width: size,
  height: size,
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: sw,
  strokeLinecap: "round",
  strokeLinejoin: "round",
  "aria-hidden": "true"
}, p), children);
const IconArrow = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("path", {
  d: "M5 12h14M13 6l6 6-6 6"
}));
const IconCheck = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("circle", {
  cx: "12",
  cy: "12",
  r: "9.5"
}), /*#__PURE__*/React.createElement("path", {
  d: "M8.5 12.5l2.4 2.4 4.6-5.2"
}));
const IconCross = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("circle", {
  cx: "12",
  cy: "12",
  r: "9.5"
}), /*#__PURE__*/React.createElement("path", {
  d: "M9 9l6 6M15 9l-6 6"
}));
const IconStar = p => /*#__PURE__*/React.createElement(Svg, _extends({}, p, {
  fill: "currentColor",
  stroke: "none"
}), /*#__PURE__*/React.createElement("path", {
  d: "M12 3l2.6 5.7 6.2.6-4.7 4.1 1.4 6.1L12 16.9 6.5 19.6l1.4-6.1L3.2 9.3l6.2-.6z"
}));
const IconMenu = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("path", {
  d: "M4 7h16M4 12h16M4 17h16"
}));
const IconClose = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("path", {
  d: "M6 6l12 12M18 6L6 18"
}));
const IconCheckBig = p => /*#__PURE__*/React.createElement(Svg, _extends({}, p, {
  sw: 2.4
}), /*#__PURE__*/React.createElement("path", {
  d: "M4 12.5l5 5L20 6.5"
}));

/* Method-step icons */
const IconCall = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("path", {
  d: "M15.5 13.5c-1.5 1.5-3 .5-4.5-1s-2.5-3-1-4.5l1-1-2.5-3.5-1.5.5C5 5 5 8 8 11.5s6.5 3.5 7.5 1.5l.5-1.5-3.5-2.5z"
}), /*#__PURE__*/React.createElement("path", {
  d: "M14 4a6 6 0 016 6"
}));
const IconPlan = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("rect", {
  x: "5",
  y: "3",
  width: "14",
  height: "18",
  rx: "2"
}), /*#__PURE__*/React.createElement("path", {
  d: "M9 8h6M9 12h6M9 16h4"
}));
const IconUsers = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("circle", {
  cx: "9",
  cy: "8",
  r: "3"
}), /*#__PURE__*/React.createElement("path", {
  d: "M3.5 20c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5"
}), /*#__PURE__*/React.createElement("path", {
  d: "M16 5.5a3 3 0 010 5.8M17.5 14.6c2 .6 3.5 2.3 3.5 4.9"
}));
const IconTrophy = p => /*#__PURE__*/React.createElement(Svg, p, /*#__PURE__*/React.createElement("path", {
  d: "M7 4h10v4a5 5 0 01-10 0V4z"
}), /*#__PURE__*/React.createElement("path", {
  d: "M7 6H4.5a2.5 2.5 0 002.5 2.5M17 6h2.5A2.5 2.5 0 0117 8.5"
}), /*#__PURE__*/React.createElement("path", {
  d: "M10 13.5h4v3h-4zM8 20h8M9.5 16.5h5"
}));
Object.assign(window, {
  IconArrow,
  IconCheck,
  IconCross,
  IconStar,
  IconMenu,
  IconClose,
  IconCheckBig,
  IconCall,
  IconPlan,
  IconUsers,
  IconTrophy
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/icons.jsx", error: String((e && e.message) || e) }); }

// ui_kits/website/sections.jsx
try { (() => {
/* Luke Goulden — website sections. Self-contained; uses .lg-* + .wk-* classes. */

/* ---- Logo lockup (G-Cut mark + wordmark) ------------------- */
const Mark = ({
  h = 26,
  color = "var(--lg-teal)"
}) => /*#__PURE__*/React.createElement("svg", {
  viewBox: "0 0 300 150",
  style: {
    height: h,
    width: "auto",
    display: "block",
    color
  },
  "aria-hidden": "true"
}, /*#__PURE__*/React.createElement("circle", {
  cx: "74",
  cy: "75",
  r: "62",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "22"
}), /*#__PURE__*/React.createElement("rect", {
  x: "74",
  y: "64",
  width: "212",
  height: "22",
  fill: "currentColor"
}));
const Lockup = ({
  color = "var(--lg-teal)",
  h = 26
}) => /*#__PURE__*/React.createElement("span", {
  style: {
    display: "inline-flex",
    alignItems: "center",
    gap: 14,
    color
  }
}, /*#__PURE__*/React.createElement(Mark, {
  h: h,
  color: color
}), /*#__PURE__*/React.createElement("span", {
  style: {
    width: 1,
    height: h * 0.92,
    background: color,
    opacity: 0.42
  }
}), /*#__PURE__*/React.createElement("span", {
  style: {
    fontFamily: "var(--font-display)",
    fontWeight: "var(--fw-medium)",
    textTransform: "uppercase",
    letterSpacing: "var(--tr-wordmark)",
    paddingRight: "0.22em",
    fontSize: h * 0.62,
    color,
    lineHeight: 1
  }
}, "Luke Goulden"));

/* ---- Header ------------------------------------------------ */
function Header({
  onApply,
  onNav
}) {
  const links = ["Coaching", "Method", "Results", "About", "Contact"];
  return /*#__PURE__*/React.createElement("header", {
    className: "wk-header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap wk-nav"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#top",
    onClick: e => onNav(e, "top")
  }, /*#__PURE__*/React.createElement(Lockup, null)), /*#__PURE__*/React.createElement("ul", {
    className: "wk-navlinks"
  }, links.map(l => /*#__PURE__*/React.createElement("li", {
    key: l
  }, /*#__PURE__*/React.createElement("a", {
    href: `#${l.toLowerCase()}`,
    onClick: e => onNav(e, l.toLowerCase())
  }, l)))), /*#__PURE__*/React.createElement("button", {
    className: "lg-btn lg-btn--coral lg-btn--sm wk-cta",
    onClick: onApply
  }, "Apply for coaching"), /*#__PURE__*/React.createElement("button", {
    className: "wk-burger",
    "aria-label": "Menu"
  }, /*#__PURE__*/React.createElement("span", null), /*#__PURE__*/React.createElement("span", null), /*#__PURE__*/React.createElement("span", null))));
}

/* ---- Hero -------------------------------------------------- */
function Hero({
  onApply,
  onNav
}) {
  return /*#__PURE__*/React.createElement("section", {
    className: "wk-hero",
    id: "top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-hero-bg"
  }), /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-hero-grid"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow on-dark"
  }, "Busy parents & professionals over 30"), /*#__PURE__*/React.createElement("h1", null, "Real change. Real results. Built for real life."), /*#__PURE__*/React.createElement("p", {
    className: "sub"
  }, "Habit-based coaching, science-backed nutrition and strength training that actually fits your week \u2014 not a version of your week that doesn't exist."), /*#__PURE__*/React.createElement("div", {
    className: "wk-hero-actions"
  }, /*#__PURE__*/React.createElement("button", {
    className: "lg-btn lg-btn--coral lg-btn--lg",
    onClick: onApply
  }, "Apply for coaching"), /*#__PURE__*/React.createElement("a", {
    className: "lg-btn lg-btn--ondark lg-btn--lg",
    href: "#method",
    onClick: e => onNav(e, "method"),
    style: {
      background: "transparent",
      color: "var(--lg-offwhite)",
      border: "1px solid var(--lg-line-onteal)"
    }
  }, "See how it works")), /*#__PURE__*/React.createElement("div", {
    className: "wk-trust"
  }, /*#__PURE__*/React.createElement("span", {
    className: "wk-stars"
  }, "\u2605\u2605\u2605\u2605\u2605"), /*#__PURE__*/React.createElement("b", null, "4.9 out of 5"), /*#__PURE__*/React.createElement("span", null, "\xB7 based on 100+ reviews"))), /*#__PURE__*/React.createElement("div", {
    className: "wk-floatcard"
  }, /*#__PURE__*/React.createElement("div", {
    className: "img"
  }, /*#__PURE__*/React.createElement("img", {
    src: "../../assets/img/tf-helen.jpg",
    alt: "Helen's transformation"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ba"
  }, /*#__PURE__*/React.createElement("span", null, "Before"), /*#__PURE__*/React.createElement("span", null, "After"))), /*#__PURE__*/React.createElement("div", {
    className: "meta"
  }, /*#__PURE__*/React.createElement("div", {
    className: "nm"
  }, "Helen"), /*#__PURE__*/React.createElement("div", {
    className: "big"
  }, "-11KG"), /*#__PURE__*/React.createElement("div", {
    className: "wks"
  }, "12 weeks")), /*#__PURE__*/React.createElement("div", {
    className: "q"
  }, "\"The best shape I've been in for years.\"")))));
}

/* ---- Press strip (text logotypes — supplied logo PNGs were corrupt) --- */
function Press() {
  const names = ["Men's Fitness", "Coach", "Women's Health", "Balance", "Health & Wellbeing"];
  return /*#__PURE__*/React.createElement("div", {
    className: "wk-press"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap row"
  }, /*#__PURE__*/React.createElement("span", {
    className: "lbl"
  }, "As featured in"), names.map(n => /*#__PURE__*/React.createElement("span", {
    key: n,
    className: "wk-press-name"
  }, n))));
}

/* ---- Problem ----------------------------------------------- */
function Problem() {
  const pains = ["You've tried every diet or plan but nothing sticks", "You feel tired, unmotivated and stuck", "You know what to do, but struggle to stay consistent", "Work, life and family always come first", "Your confidence has taken a hit", "You start strong, but always fall off track"];
  return /*#__PURE__*/React.createElement("section", {
    className: "wk-problem",
    id: "coaching"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "grid"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow"
  }, "Sound familiar?"), /*#__PURE__*/React.createElement("div", {
    className: "wk-sectionhead",
    style: {
      marginBottom: "1.8rem"
    }
  }, /*#__PURE__*/React.createElement("h2", null, "You don't need more discipline. You need a plan that fits.")), /*#__PURE__*/React.createElement("ul", {
    role: "list"
  }, pains.map(p => /*#__PURE__*/React.createElement("li", {
    className: "lg-tick",
    key: p,
    style: {
      listStyle: "none"
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "lg-tick__icon",
    style: {
      color: "var(--lg-coral)"
    }
  }, /*#__PURE__*/React.createElement(IconCheck, {
    size: 20
  })), /*#__PURE__*/React.createElement("span", null, p))))), /*#__PURE__*/React.createElement("div", {
    className: "photo"
  }, /*#__PURE__*/React.createElement("img", {
    src: "../../assets/img/lifestyle.jpg",
    alt: "Real life"
  })))));
}

/* ---- Method ------------------------------------------------ */
function Method() {
  const steps = [[/*#__PURE__*/React.createElement(IconCall, {
    size: 28
  }), "Game-plan call", "We get to know you, your goals and the life you're actually living — then build your roadmap."], [/*#__PURE__*/React.createElement(IconPlan, {
    size: 28
  }), "Personalised plan", "Training, nutrition and habit strategies built around your week, your body and your equipment."], [/*#__PURE__*/React.createElement(IconUsers, {
    size: 28
  }), "Weekly accountability", "Check-ins, honest feedback and adjustments. Kindness and candour, never one without the other."], [/*#__PURE__*/React.createElement(IconTrophy, {
    size: 28
  }), "Results that last", "Strength, energy and confidence you keep — designed for the next ten years, not ten weeks."]];
  return /*#__PURE__*/React.createElement("section", {
    className: "wk-method",
    id: "method"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-sectionhead center"
  }, /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow"
  }, "The method"), /*#__PURE__*/React.createElement("h2", null, "How coaching works")), /*#__PURE__*/React.createElement("div", {
    className: "wk-steps"
  }, steps.map(([ico, t, d], i) => /*#__PURE__*/React.createElement("div", {
    className: "wk-step",
    key: t
  }, /*#__PURE__*/React.createElement("div", {
    className: "ix"
  }, /*#__PURE__*/React.createElement("span", {
    className: "num"
  }, i + 1), /*#__PURE__*/React.createElement("span", {
    className: "ico"
  }, ico)), /*#__PURE__*/React.createElement("h3", null, t), /*#__PURE__*/React.createElement("p", null, d))))));
}

/* ---- Results ----------------------------------------------- */
function Results() {
  const tf = [["tf-craig.jpg", "Craig", "-16.6KG", "12 Weeks"], ["tf-helen.jpg", "Helen", "-11KG", "12 Weeks"], ["tf-jono.jpg", "Jono", "-50LBS+", "6 Months"], ["tf-scott.jpg", "Scott", "-45LBS", "5 Months"], ["tf-richie.jpg", "Richie", "-14LBS", "Before his wedding"]];
  return /*#__PURE__*/React.createElement("section", {
    className: "wk-results",
    id: "results"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-sectionhead"
  }, /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow"
  }, "Client proof"), /*#__PURE__*/React.createElement("h2", null, "Real people. Real results.")), /*#__PURE__*/React.createElement("div", {
    className: "wk-tfgrid"
  }, tf.map(([img, nm, res, wk]) => /*#__PURE__*/React.createElement("div", {
    className: "lg-transform",
    key: nm
  }, /*#__PURE__*/React.createElement("img", {
    src: `../../assets/img/${img}`,
    alt: `${nm} — ${res}`
  }), /*#__PURE__*/React.createElement("div", {
    className: "lg-transform__cap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "lg-transform__name"
  }, nm), /*#__PURE__*/React.createElement("div", {
    className: "lg-transform__result"
  }, res), /*#__PURE__*/React.createElement("div", {
    className: "lg-transform__weeks"
  }, wk)))))));
}

/* ---- Proof band -------------------------------------------- */
function Proof() {
  return /*#__PURE__*/React.createElement("section", {
    className: "wk-proof"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "grid"
  }, /*#__PURE__*/React.createElement("figure", {
    className: "lg-quote",
    style: {
      margin: 0,
      borderColor: "var(--lg-coral)"
    }
  }, /*#__PURE__*/React.createElement("blockquote", {
    className: "lg-quote__text",
    style: {
      margin: 0,
      color: "var(--lg-offwhite)"
    }
  }, "\"I've started a hundred times before. This is the first time it actually stuck \u2014 because it finally fit my life.\""), /*#__PURE__*/React.createElement("figcaption", {
    className: "lg-quote__cite",
    style: {
      color: "var(--text-on-dark-muted)"
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "lg-avatar",
    style: {
      width: 36,
      height: 36,
      fontSize: 13
    }
  }, /*#__PURE__*/React.createElement("img", {
    src: "../../assets/img/tf-scott.jpg",
    alt: "Scott"
  })), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", {
    style: {
      color: "var(--lg-offwhite)"
    }
  }, "Scott"), " \xB7 lost 45lbs in 5 months"))), /*#__PURE__*/React.createElement("div", {
    className: "stats"
  }, /*#__PURE__*/React.createElement("div", {
    className: "lg-stat lg-stat--ondark"
  }, /*#__PURE__*/React.createElement("span", {
    className: "lg-stat__num"
  }, "100+"), /*#__PURE__*/React.createElement("span", {
    className: "lg-stat__label"
  }, "Clients helped")), /*#__PURE__*/React.createElement("div", {
    className: "lg-stat lg-stat--ondark"
  }, /*#__PURE__*/React.createElement("span", {
    className: "lg-stat__num"
  }, "4.9\u2605"), /*#__PURE__*/React.createElement("span", {
    className: "lg-stat__label"
  }, "100+ reviews")), /*#__PURE__*/React.createElement("div", {
    className: "lg-stat lg-stat--ondark"
  }, /*#__PURE__*/React.createElement("span", {
    className: "lg-stat__num"
  }, "10yr"), /*#__PURE__*/React.createElement("span", {
    className: "lg-stat__label"
  }, "Built to last"))))));
}

/* ---- Included / different ---------------------------------- */
function Included() {
  const inc = ["Personalised training programme", "Nutrition guidance that fits real life", "Weekly check-ins & adjustments", "Habit coaching", "Progress tracking", "Direct coach support", "Educational resources", "Trainerize app access"];
  const diff = ["No quick fixes or crash diets", "No meal-replacement shakes", "No unrealistic restrictions", "No cookie-cutter plans"];
  return /*#__PURE__*/React.createElement("section", {
    className: "wk-included",
    id: "about"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "grid"
  }, /*#__PURE__*/React.createElement("div", {
    className: "left"
  }, /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow"
  }, "What's included"), /*#__PURE__*/React.createElement("h2", {
    style: {
      fontSize: "1.6rem"
    }
  }, "Everything you need, nothing you don't"), /*#__PURE__*/React.createElement("ul", {
    role: "list",
    style: {
      gridTemplateColumns: "1fr 1fr"
    }
  }, inc.map(i => /*#__PURE__*/React.createElement("li", {
    className: "lg-tick",
    key: i,
    style: {
      listStyle: "none",
      fontSize: "0.92rem"
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "lg-tick__icon"
  }, /*#__PURE__*/React.createElement(IconCheck, {
    size: 18
  })), /*#__PURE__*/React.createElement("span", null, i))))), /*#__PURE__*/React.createElement("div", {
    className: "right"
  }, /*#__PURE__*/React.createElement("div", {
    className: "lg-card lg-card--calm"
  }, /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow"
  }, "Why we're different"), /*#__PURE__*/React.createElement("h2", {
    style: {
      fontSize: "1.6rem"
    }
  }, "We coach adults."), /*#__PURE__*/React.createElement("ul", {
    role: "list"
  }, diff.map(d => /*#__PURE__*/React.createElement("li", {
    className: "lg-tick lg-tick--cross",
    key: d,
    style: {
      listStyle: "none",
      fontSize: "0.92rem"
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "lg-tick__icon"
  }, /*#__PURE__*/React.createElement(IconCross, {
    size: 18
  })), /*#__PURE__*/React.createElement("span", null, d)))), /*#__PURE__*/React.createElement("p", {
    style: {
      marginTop: "1.2rem",
      marginBottom: 0,
      fontSize: "0.92rem",
      color: "var(--text-muted)"
    }
  }, "Just proven strategies, ongoing support and a coach who tells you the truth."))))));
}

/* ---- FAQ --------------------------------------------------- */
function FAQ() {
  const faqs = [["How much time do I need each week?", "Most clients see results with 3–4 sessions of 30–60 minutes, plus daily nutrition habits. The plan is built around your schedule — not the other way around."], ["Do I need a gym membership?", "No. We build your programme around a full gym, a home setup, or bodyweight only. The plan adapts to your equipment."], ["Can I still eat out and enjoy food?", "Absolutely — sustainability is the point. We build habits that fit real life, including meals out, social events and a glass of wine."], ["What if I travel or have a busy job?", "Coaching is designed for exactly this. We plan around busy weeks, travel and family life with flexible training and simple nutrition rules."], ["How long is the programme?", "Most clients commit to 12+ weeks because real change takes time — but you'll feel the difference far sooner. We keep going as long as you want support."]];
  return /*#__PURE__*/React.createElement("section", {
    className: "wk-faq",
    id: "contact"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "grid"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow"
  }, "Questions"), /*#__PURE__*/React.createElement("h2", {
    style: {
      fontSize: "1.8rem"
    }
  }, "Honest answers, before you apply")), /*#__PURE__*/React.createElement("div", {
    className: "lg-accordion"
  }, faqs.map(([q, a], i) => /*#__PURE__*/React.createElement("details", {
    className: "lg-accordion__item",
    key: q,
    open: i === 0
  }, /*#__PURE__*/React.createElement("summary", {
    className: "lg-accordion__head"
  }, /*#__PURE__*/React.createElement("span", null, q), /*#__PURE__*/React.createElement("span", {
    className: "lg-accordion__icon"
  }, /*#__PURE__*/React.createElement(IconArrow, {
    size: 18
  }))), /*#__PURE__*/React.createElement("div", {
    className: "lg-accordion__body"
  }, a)))))));
}

/* ---- Closing CTA ------------------------------------------- */
function Closing({
  onApply
}) {
  return /*#__PURE__*/React.createElement("section", {
    className: "wk-closing"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap"
  }, /*#__PURE__*/React.createElement("p", {
    className: "wk-eyebrow on-dark",
    style: {
      textAlign: "center"
    }
  }, "Start here"), /*#__PURE__*/React.createElement("h2", null, "Start where you are. Then keep going."), /*#__PURE__*/React.createElement("p", null, "You don't need another diet. You need a plan, support and accountability \u2014 built for the life you actually live."), /*#__PURE__*/React.createElement("button", {
    className: "lg-btn lg-btn--coral lg-btn--lg",
    onClick: onApply
  }, "Apply for coaching"), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: "1rem",
      fontSize: "0.8rem",
      color: "var(--text-on-dark-muted)"
    }
  }, "Spots are limited \u2014 applications reviewed weekly.")));
}

/* ---- Footer ------------------------------------------------ */
function Footer() {
  return /*#__PURE__*/React.createElement("footer", {
    className: "wk-footer"
  }, /*#__PURE__*/React.createElement("div", {
    className: "wk-wrap row"
  }, /*#__PURE__*/React.createElement(Lockup, {
    color: "var(--lg-offwhite)",
    h: 22
  }), /*#__PURE__*/React.createElement("div", {
    className: "links"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Privacy"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Terms"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Instagram"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Podcast")), /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "0.78rem"
    }
  }, "\xA9 2026 Luke Goulden. Real Change \xB7 Real Results.")));
}
Object.assign(window, {
  Mark,
  Lockup,
  Header,
  Hero,
  Press,
  Problem,
  Method,
  Results,
  Proof,
  Included,
  FAQ,
  Closing,
  Footer
});
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/website/sections.jsx", error: String((e && e.message) || e) }); }

__ds_ns.GCutMark = __ds_scope.GCutMark;

__ds_ns.Logo = __ds_scope.Logo;

__ds_ns.Button = __ds_scope.Button;

__ds_ns.Accordion = __ds_scope.Accordion;

__ds_ns.Card = __ds_scope.Card;

__ds_ns.Testimonial = __ds_scope.Testimonial;

__ds_ns.TickItem = __ds_scope.TickItem;

__ds_ns.Transformation = __ds_scope.Transformation;

__ds_ns.Avatar = __ds_scope.Avatar;

__ds_ns.Badge = __ds_scope.Badge;

__ds_ns.Stat = __ds_scope.Stat;

__ds_ns.Tag = __ds_scope.Tag;

__ds_ns.Field = __ds_scope.Field;

__ds_ns.Input = __ds_scope.Input;

__ds_ns.Textarea = __ds_scope.Textarea;

})();
