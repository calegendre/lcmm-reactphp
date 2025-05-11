// Preload critical images
document.addEventListener('DOMContentLoaded', function() {
    // Preload logo.png
    var logoPreload = new Image();
    logoPreload.src = '/logo.png';
    
    // Preload coverunavailable.png
    var coverPreload = new Image();
    coverPreload.src = '/coverunavailable.png';
    
    console.log('Preloaded critical images');
});