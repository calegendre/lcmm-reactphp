import React from 'react';
import Navbar from './Navbar';

const Layout = ({ children }) => {
  return (
    <div className="flex flex-col min-h-screen bg-black">
      <Navbar />
      <main className="flex-grow">
        {children}
      </main>
      <footer className="bg-dark-100 border-t border-dark-300 py-4">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center">
            <div className="text-gray-400 text-sm">
              © {new Date().getFullYear()} Legendre Cloud Media Manager
            </div>
            <div className="text-gray-400 text-sm">
              LCMM
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Layout;
