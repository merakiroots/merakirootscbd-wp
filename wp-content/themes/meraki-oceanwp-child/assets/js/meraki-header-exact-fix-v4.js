(function () {
  "use strict";

  var lastKnownHeaderHeight = 0;
  var ticking = false;

  function q(selector) {
    return document.querySelector(selector);
  }

  function isMobile() {
    return window.matchMedia("(max-width: 767px)").matches;
  }

  function getActiveHeader() {
    return isMobile() ? q(".mr-mobile-header") : q(".mr-header");
  }

  function getAnnouncementBar() {
    return q(".mr-announcement") ||
      q(".mr-topbar") ||
      q(".mr-free-shipping") ||
      q(".mr-shipping-bar");
  }

  function measureHeaderStack() {
    var header = getActiveHeader();
    if (!header) return;

    var announcement = getAnnouncementBar();
    var announcementHeight = announcement ? Math.ceil(announcement.getBoundingClientRect().height || announcement.offsetHeight || 0) : 0;

    document.documentElement.style.setProperty("--mr-announcement-offset", announcementHeight + "px");

    var headerHeight = Math.ceil(header.getBoundingClientRect().height || header.offsetHeight || 0);
    if (!headerHeight) headerHeight = isMobile() ? 66 : 82;

    var total = announcementHeight + headerHeight;
    if (total < 1) total = isMobile() ? 66 : 82;

    if (Math.abs(total - lastKnownHeaderHeight) > 1) {
      document.body.style.setProperty("--mr-header-stack-height", total + "px");
      lastKnownHeaderHeight = total;
    }

    document.body.classList.add("mr-exact-header-ready");
  }

  function getCollapseThreshold() {
    var candidates = [
      ".mr-home-hero",
      ".home .wp-block-cover:first-of-type",
      ".home .entry-content > *:first-child",
      ".home main > *:first-child",
      ".woocommerce-products-header",
      ".page-header"
    ];

    for (var i = 0; i < candidates.length; i++) {
      var el = q(candidates[i]);
      if (!el) continue;

      var height = Math.ceil(el.getBoundingClientRect().height || el.offsetHeight || 0);
      if (height > 180) return Math.max(120, height - 90);
    }

    return 120;
  }

  function update() {
    ticking = false;

    var threshold = getCollapseThreshold();

    if (window.scrollY > threshold) {
      document.body.classList.add("mr-header-condensed");
    } else {
      document.body.classList.remove("mr-header-condensed");
    }

    measureHeaderStack();
  }

  function requestUpdate() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(update);
  }

  function boot() {
    measureHeaderStack();
    update();
    setTimeout(update, 250);
    setTimeout(update, 1000);
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
