import React from 'react';
import { Link } from 'react-router-dom';

const NotFoundPage = () => {
  return (
    <div className="min-h-screen flex items-center justify-center bg-black px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full text-center">
        <div className="text-center mb-8">
          <div className="logo-container mx-auto mb-4">
            {/* Logo placeholder - will be replaced with actual logo */}
            <div className="text-4xl font-extrabold text-white">
              <span className="text-primary-500">L</span>CMM
            </div>
          </div>
          <h1 className="text-5xl font-bold text-white">404</h1>
          <h2 className="text-2xl font-medium text-gray-400 mt-4">Page Not Found</h2>
          <p className="text-gray-500 mt-2">The page you're looking for doesn't exist or has been moved.</p>
        </div>
        
        <div className="flex justify-center mt-8">
          <Link to="/" className="btn-primary">
            Go to Home
          </Link>
        </div>
      </div>
    </div>
  );
};

export default NotFoundPage;
