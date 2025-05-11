/*
 * LCMM - Legendre Cloud Media Manager - Components Bundle
 * Version: 1.0.1
 * This file contains compiled components for LCMM
 */

// Map the Components export
window.LCMM = window.LCMM || {};
window.LCMM.Components = window.LCMM.Components || {};

// Load the image fallback handler
try {
  const imageFallbackScript = document.createElement('script');
  imageFallbackScript.src = '/static/js/image-fallback.js';
  imageFallbackScript.async = false;
  document.head.appendChild(imageFallbackScript);
  
  console.log('LCMM: Loaded image fallback script');
} catch (error) {
  console.error('LCMM: Failed to load image fallback script', error);
}

// Load the Navbar component
try {
  const NavbarComponent = document.createElement('script');
  NavbarComponent.src = '/static/js/components/Navbar.js';
  NavbarComponent.async = true;
  document.head.appendChild(NavbarComponent);
  
  console.log('LCMM: Loaded Navbar component');
} catch (error) {
  console.error('LCMM: Failed to load Navbar component', error);
}

// Load the Additions component
try {
  const AdditionsComponent = document.createElement('script');
  AdditionsComponent.src = '/static/js/components/Additions.js';
  AdditionsComponent.async = true;
  document.head.appendChild(AdditionsComponent);
  
  console.log('LCMM: Loaded Additions component');
} catch (error) {
  console.error('LCMM: Failed to load Additions component', error);
}

// Load the AdminPage component
try {
  const AdminPageComponent = document.createElement('script');
  AdminPageComponent.src = '/static/js/components/AdminPage.js';
  AdminPageComponent.async = true;
  document.head.appendChild(AdminPageComponent);
  
  console.log('LCMM: Loaded AdminPage component');
} catch (error) {
  console.error('LCMM: Failed to load AdminPage component', error);
}

// Load the MediaCard component
try {
  const MediaCardComponent = document.createElement('script');
  MediaCardComponent.src = '/static/js/components/MediaCard.js';
  MediaCardComponent.async = true;
  document.head.appendChild(MediaCardComponent);
  
  console.log('LCMM: Loaded MediaCard component');
} catch (error) {
  console.error('LCMM: Failed to load MediaCard component', error);
}

// Load the SearchResult component
try {
  const SearchResultComponent = document.createElement('script');
  SearchResultComponent.src = '/static/js/components/SearchResult.js';
  SearchResultComponent.async = true;
  document.head.appendChild(SearchResultComponent);
  
  console.log('LCMM: Loaded SearchResult component');
} catch (error) {
  console.error('LCMM: Failed to load SearchResult component', error);
}

// Load the LoginPage component
try {
  const LoginPageComponent = document.createElement('script');
  LoginPageComponent.src = '/static/js/components/LoginPage.js';
  LoginPageComponent.async = true;
  document.head.appendChild(LoginPageComponent);
  
  console.log('LCMM: Loaded LoginPage component');
} catch (error) {
  console.error('LCMM: Failed to load LoginPage component', error);
}

// Load the RegisterPage component
try {
  const RegisterPageComponent = document.createElement('script');
  RegisterPageComponent.src = '/static/js/components/RegisterPage.js';
  RegisterPageComponent.async = true;
  document.head.appendChild(RegisterPageComponent);
  
  console.log('LCMM: Loaded RegisterPage component');
} catch (error) {
  console.error('LCMM: Failed to load RegisterPage component', error);
}

// Register the bundle as loaded
console.log('LCMM Components Bundle Loaded');