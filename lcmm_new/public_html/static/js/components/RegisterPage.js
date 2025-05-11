import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import RegisterForm from '../components/auth/RegisterForm';
import { useAuth } from '../contexts/AuthContext';

const RegisterPage = () => {
  const { user } = useAuth();
  const navigate = useNavigate();

  // If user is already logged in, redirect to library page
  useEffect(() => {
    if (user) {
      navigate('/library');
    }
  }, [user, navigate]);

  return (
    <div className="min-h-screen flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-black to-dark-100">
      <div className="w-full max-w-md">
        <div className="text-center mb-10">
          <div className="logo-container mx-auto mb-4">
            {/* Logo element with absolute path */}
            <img 
              src="/logo.png" 
              alt="LCMM Logo" 
              className="mx-auto mb-4" 
              style={{ height: "80px", width: "auto" }}
            />
            <div className="text-4xl font-extrabold text-white"> 
              <span className="text-primary-500">L</span>CMM 
            </div>
          </div>
          <h1 className="text-2xl font-bold text-white">Legendre Cloud Media Manager</h1>
        </div>
        
        <RegisterForm />
      </div>
    </div>
  );
};

export default RegisterPage;