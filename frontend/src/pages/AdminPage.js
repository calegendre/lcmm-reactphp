import React, { useState } from 'react';
import { Routes, Route, Link, useLocation } from 'react-router-dom';
import UsersManagement from '../components/admin/UsersManagement';
import InvitationsManagement from '../components/admin/InvitationsManagement';
import ActivityLogs from '../components/admin/ActivityLogs';

const AdminPage = () => {
  const location = useLocation();
  const [currentTab, setCurrentTab] = useState(
    location.pathname === '/admin/invitations' ? 'invitations' :
    location.pathname === '/admin/activity' ? 'activity' : 'users'
  );

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-white">Admin Dashboard</h1>
        <p className="text-gray-400 mt-2">Manage users, invitations, and view activity logs</p>
      </div>

      <div className="bg-dark-100 rounded-lg border border-dark-300 overflow-hidden">
        <div className="flex border-b border-dark-300">
          <Link
            to="/admin"
            onClick={() => setCurrentTab('users')}
            className={`px-4 py-3 font-medium text-sm transition-colors duration-200 ${
              currentTab === 'users'
                ? 'border-b-2 border-primary-500 text-primary-500'
                : 'text-gray-400 hover:text-white'
            }`}
          >
            Users
          </Link>
          <Link
            to="/admin/invitations"
            onClick={() => setCurrentTab('invitations')}
            className={`px-4 py-3 font-medium text-sm transition-colors duration-200 ${
              currentTab === 'invitations'
                ? 'border-b-2 border-primary-500 text-primary-500'
                : 'text-gray-400 hover:text-white'
            }`}
          >
            Invitations
          </Link>
          <Link
            to="/admin/activity"
            onClick={() => setCurrentTab('activity')}
            className={`px-4 py-3 font-medium text-sm transition-colors duration-200 ${
              currentTab === 'activity'
                ? 'border-b-2 border-primary-500 text-primary-500'
                : 'text-gray-400 hover:text-white'
            }`}
          >
            Activity Logs
          </Link>
        </div>

        <div className="p-6">
          <Routes>
            <Route path="/" element={<UsersManagement />} />
            <Route path="/invitations" element={<InvitationsManagement />} />
            <Route path="/activity" element={<ActivityLogs />} />
          </Routes>
        </div>
      </div>
    </div>
  );
};

export default AdminPage;
