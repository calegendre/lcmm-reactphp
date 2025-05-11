
/**
 * LCMM Image Fixes
 * 
 * This script fixes all image loading issues in the LCMM application.
 * Copy this file to your public_html directory and include it in your index.html.
 * 
 * It handles:
 * 1. Logo in the header
 * 2. Logo on login/signup pages
 * 3. Cover art fallbacks in media cards
 */

// Run this script as soon as the DOM starts loading
document.addEventListener('DOMContentLoaded', function() {
  // Fix 1: Logo in navbar
  const navbarLogos = document.querySelectorAll('.logo-container img');
  navbarLogos.forEach(logo => {
    logo.onerror = function() {
      // If logo fails to load, try the absolute URL
      this.src = 'https://lcmm.legendre.cloud/logo.png';
    };
  });
  
  // Fix 2: Login/registration page logos
  const loginLogos = document.querySelectorAll('.min-h-screen .logo-container img');
  loginLogos.forEach(logo => {
    logo.onerror = function() {
      // If logo fails to load, try the absolute URL
      this.src = 'https://lcmm.legendre.cloud/logo.png';
    };
  });
  
  // Fix 3: Cover unavailable images
  const mediaImages = document.querySelectorAll('.media-card img, [class*="w-full sm:w-36"] img');
  mediaImages.forEach(img => {
    img.addEventListener('error', function() {
      // If media image fails to load, use the fallback
      this.src = 'https://lcmm.legendre.cloud/coverunavailable.png';
    });
  });
  
  console.log('LCMM Image Fix Script Loaded');
});

// Additional fix for dynamically loaded content
const observer = new MutationObserver(function(mutations) {
  mutations.forEach(function(mutation) {
    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
      // Check if new images were added
      const newImages = document.querySelectorAll('img:not([data-lcmm-processed])');
      newImages.forEach(img => {
        // Mark as processed to avoid duplicates
        img.dataset.lcmmProcessed = 'true';
        
        // Handle based on context
        if (img.closest('.logo-container')) {
          img.onerror = function() {
            this.src = 'https://lcmm.legendre.cloud/logo.png';
          };
        } else if (img.closest('.media-card') || img.closest('[class*="w-full sm:w-36"]')) {
          img.onerror = function() {
            this.src = 'https://lcmm.legendre.cloud/coverunavailable.png';
          };
        }
      });
    }
  });
});

// Start observing the document
observer.observe(document.body, {
  childList: true,
  subtree: true
});
      