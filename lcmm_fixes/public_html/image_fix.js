/**
 * LCMM Image Fixes
 * 
 * This script fixes all image loading issues in the LCMM application.
 * Include this in your index.html immediately after the title tag.
 * 
 * It handles:
 * 1. Logo in the header
 * 2. Logo on login/signup pages
 * 3. Cover art fallbacks in media cards
 */

// Run this script as soon as the DOM starts loading
document.addEventListener('DOMContentLoaded', function() {
  console.log('LCMM Image Fix Script Started');

  // Fix 1: Logo in navbar
  const navbarLogos = document.querySelectorAll('.logo-container img');
  navbarLogos.forEach(logo => {
    console.log('Found navbar logo:', logo);
    logo.onerror = function() {
      console.log('Navbar logo error, using fallback');
      // If logo fails to load, try the absolute URL
      this.src = 'https://lcmm.legendre.cloud/logo.png';
    };
  });
  
  // Fix 2: Login/registration page logos
  const loginLogos = document.querySelectorAll('.min-h-screen .logo-container img');
  loginLogos.forEach(logo => {
    console.log('Found login logo:', logo);
    logo.onerror = function() {
      console.log('Login logo error, using fallback');
      // If logo fails to load, try the absolute URL
      this.src = 'https://lcmm.legendre.cloud/logo.png';
    };
  });
  
  // Fix 3: Cover unavailable images
  const mediaImages = document.querySelectorAll('.media-card img, [class*="w-full sm:w-36"] img');
  mediaImages.forEach(img => {
    console.log('Found media image:', img);
    img.addEventListener('error', function() {
      console.log('Media image error, using fallback');
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
            console.log('Dynamic logo error, using fallback');
            this.src = 'https://lcmm.legendre.cloud/logo.png';
          };
        } else if (img.closest('.media-card') || img.closest('[class*="w-full sm:w-36"]')) {
          img.onerror = function() {
            console.log('Dynamic media image error, using fallback');
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

// Add a global error handler for all images
window.addEventListener('error', function(e) {
  if (e.target.tagName === 'IMG') {
    console.log('Global image error for:', e.target.src);
    
    // Check if this is a logo
    if (e.target.closest('.logo-container')) {
      e.target.src = 'https://lcmm.legendre.cloud/logo.png';
    } 
    // Check if this is a media image
    else if (e.target.closest('.media-card') || e.target.closest('[class*="w-full sm:w-36"]')) {
      e.target.src = 'https://lcmm.legendre.cloud/coverunavailable.png';
    }
  }
}, true);