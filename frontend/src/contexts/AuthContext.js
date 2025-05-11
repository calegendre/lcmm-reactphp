import React, { createContext, useState, useContext, useEffect } from 'react';
import axios from 'axios';

const AuthContext = createContext();

export const useAuth = () => useContext(AuthContext);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const backendUrl = process.env.REACT_APP_BACKEND_URL || '';

  // Initialize auth state from localStorage
  useEffect(() => {
    const token = localStorage.getItem('lcmm_token');
    const storedUser = localStorage.getItem('lcmm_user');

    if (token && storedUser) {
      try {
        setUser(JSON.parse(storedUser));
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      } catch (err) {
        console.error('Failed to parse stored user data', err);
        logout();
      }
    }
    setLoading(false);
  }, []);

  const login = async (email, password) => {
    setLoading(true);
    setError(null);
    try {
      const response = await axios.post(`${backendUrl}/api/auth?action=login`, {
        email,
        password
      });

      const { token, user } = response.data;
      
      // Store token in axios defaults
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      // Store auth data in localStorage
      localStorage.setItem('lcmm_token', token);
      localStorage.setItem('lcmm_user', JSON.stringify(user));
      
      setUser(user);
      setLoading(false);
      return user;
    } catch (err) {
      setLoading(false);
      const message = err.response?.data?.error || 'Login failed. Please check your credentials.';
      setError(message);
      throw new Error(message);
    }
  };

  const register = async (email, password, invitationCode) => {
    setLoading(true);
    setError(null);
    try {
      const response = await axios.post(`${backendUrl}/api/auth?action=register`, {
        email,
        password,
        invitation_code: invitationCode
      });

      const { token, user } = response.data;

      // Store token in axios defaults
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      // Store in localStorage
      localStorage.setItem('lcmm_token', token);
      localStorage.setItem('lcmm_user', JSON.stringify(user));
      
      setUser(user);
      setLoading(false);
      return user;
    } catch (err) {
      setLoading(false);
      const message = err.response?.data?.error || 'Registration failed. Please try again.';
      setError(message);
      throw new Error(message);
    }
  };

  const logout = () => {
    // Remove from localStorage
    localStorage.removeItem('lcmm_token');
    localStorage.removeItem('lcmm_user');
    
    // Clear axios headers
    delete axios.defaults.headers.common['Authorization'];
    
    setUser(null);
  };

  const isAdmin = () => {
    return user?.role === 'admin';
  };

  return (
    <AuthContext.Provider value={{ 
      user, 
      loading, 
      error,
      login, 
      register, 
      logout,
      isAdmin
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
