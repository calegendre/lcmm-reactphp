/**
 * Image Fallback Handler
 * This script handles fallback for images that don't load properly
 */

document.addEventListener('DOMContentLoaded', function() {
  console.log('Image fallback handler initialized');
  
  // Function to check if an image exists
  function checkImageExists(imageUrl, callback) {
    const img = new Image();
    img.onload = function() {
      callback(true);
    };
    img.onerror = function() {
      callback(false);
    };
    img.src = imageUrl;
  }
  
  // Check if logo.png exists
  checkImageExists('/logo.png', function(exists) {
    if (!exists) {
      console.error('logo.png not found. Using text-only logo.');
      
      // Hide all instances of the logo image
      const logoImages = document.querySelectorAll('.logo-container img');
      logoImages.forEach(img => {
        img.style.display = 'none';
      });
    }
  });
  
  // Check if coverunavailable.png exists
  checkImageExists('/coverunavailable.png', function(exists) {
    if (!exists) {
      console.error('coverunavailable.png not found. Using text placeholder.');
      
      // Replace media card images that fail to load with text placeholders
      const mediaCardImages = document.querySelectorAll('.media-card img, .w-full.sm\\:w-36.md\\:w-48 img');
      mediaCardImages.forEach(img => {
        img.onerror = function() {
          const parent = this.parentElement;
          this.style.display = 'none';
          
          // Only add placeholder if it doesn't exist already
          if (!parent.querySelector('.image-placeholder')) {
            const placeholder = document.createElement('div');
            placeholder.className = 'image-placeholder bg-dark-300 flex items-center justify-center h-full w-full';
            placeholder.style.minHeight = '250px';
            placeholder.innerHTML = '<div class="text-gray-500 text-center p-4">No Image Available</div>';
            parent.appendChild(placeholder);
          }
        };
      });
    }
  });
  
  // Register global image error handler
  document.addEventListener('error', function(event) {
    if (event.target.tagName.toLowerCase() === 'img') {
      console.warn('Image failed to load:', event.target.src);
      
      // If the image is a cover/poster image
      if (event.target.closest('.media-card') || event.target.closest('.w-full.sm\\:w-36.md\\:w-48')) {
        event.target.src = '/coverunavailable.png';
      }
    }
  }, true);
});