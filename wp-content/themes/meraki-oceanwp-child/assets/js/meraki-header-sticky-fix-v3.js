(function () {
  "use strict";

  var header = null;
  var logoImg = null;
  var ticking = false;

  function getHeader() {
    return document.querySelector("#site-header") ||
      document.querySelector("header.site-header") ||
      document.querySelector("#masthead") ||
      document.querySelector("header");
  }

  function getHeaderLogoImage(h) {
    if (!h) return null;

    var preferred = h.querySelector("#site-logo img") ||
      h.querySelector("#site-logo-inner img") ||
      h.querySelector("img.custom-logo") ||
      h.querySelector(".custom-logo-link img") ||
      h.querySelector(".site-logo img") ||
      h.querySelector(".site-branding img");

    if (preferred) return preferred;

    var imgs = Array.prototype.slice.call(h.querySelectorAll("img"));
    if (!imgs.length) return null;

    imgs.sort(function (a, b) {
      var ar = (a.naturalWidth || a.width || 0) * (a.naturalHeight || a.height || 0);
      var br = (b.naturalWidth || b.width || 0) * (b.naturalHeight || b.height || 0);
      return br - ar;
    });

    return imgs[0] || null;
  }

  function markLogo() {
    header = getHeader();
    if (!header) return;

    logoImg = getHeaderLogoImage(header);
    if (!logoImg) return;

    logoImg.classList.add("mr-header-logo-img");

    var wrap =
      logoImg.closest("#site-logo") ||
      logoImg.closest("#site-logo-inner") ||
      logoImg.closest(".custom-logo-link") ||
      logoImg.closest(".site-branding") ||
      logoImg.parentElement;

    if (wrap) wrap.classList.add("mr-header-logo-wrap");
  }

  function setBodyHeaderSpace() {
    if (!header) header = getHeader();
    if (!header) return;

    var rect = header.getBoundingClientRect();
    var height = Math.max(56, Math.ceil(rect.height || header.offsetHeight || 0));

    document.body.style.setProperty("--mr-fixed-header-space", height + "px");
    document.body.classList.add("mr-header-fixed-ready");
  }

  function getCollapseThreshold() {
    var hero =
      document.querySelector(".mr-home-hero") ||
      document.querySelector(".home .wp-block-cover:first-of-type") ||
      document.querySelector(".home .elementor-section:first-of-type") ||
      document.querySelector(".home .entry-content > *:first-child");

    if (hero) {
      var height = hero.getBoundingClientRect().height || hero.offsetHeight || 0;
      if (height > 220) {
        return Math.max(120, height - 90);
      }
    }

    return 140;
  }

  function updateHeaderState() {
    ticking = false;

    if (!header) header = getHeader();
    if (!header) return;

    var threshold = getCollapseThreshold();

    if (window.scrollY > threshold) {
      document.body.classList.add("mr-header-condensed");
    } else {
      document.body.classList.remove("mr-header-condensed");
    }

    setBodyHeaderSpace();
  }

  function requestUpdate() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(updateHeaderState);
  }

  function boot() {
    markLogo();
    setBodyHeaderSpace();
    updateHeaderState();

    setTimeout(function () {
      markLogo();
      setBodyHeaderSpace();
      updateHeaderState();
    }, 500);
  }

  window.addEventListener("scroll", requestUpdate, { passive: true });
  window.addEventListener("resize", requestUpdate);
  window.addEventListener("load", boot);

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
