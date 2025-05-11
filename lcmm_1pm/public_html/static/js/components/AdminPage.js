import React, { useState, useEffect } from 'react';

const AdminPage = () => {
  const [activeTab, setActiveTab] = useState('users');
  const [users, setUsers] = useState([]);
  const [invitations, setInvitations] = useState([]);
  const [activityLogs, setActivityLogs] = useState([]);
  const [additions, setAdditions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchAdminData();
  }, []);

  const fetchAdminData = async () => {
    try {
      const token = localStorage.getItem('token');
      
      if (!token) {
        setError('Authentication required. Please log in.');
        setLoading(false);
        return;
      }
      
      // Fetch all data in parallel
      const [usersRes, invitationsRes, activityRes, additionsRes] = await Promise.all([
        fetch('/api/users', {
          headers: { 'Authorization': `Bearer ${token}` }
        }),
        fetch('/api/invitations', {
          headers: { 'Authorization': `Bearer ${token}` }
        }),
        fetch('/api/activity', {
          headers: { 'Authorization': `Bearer ${token}` }
        }),
        fetch('/api/additions', {
          headers: { 'Authorization': `Bearer ${token}` }
        })
      ]);
      
      // Check for successful responses
      if (!usersRes.ok || !invitationsRes.ok || !activityRes.ok || !additionsRes.ok) {
        throw new Error('Error fetching admin data');
      }
      
      // Parse responses
      const userData = await usersRes.json();
      const invitationsData = await invitationsRes.json();
      const activityData = await activityRes.json();
      const additionsData = await additionsRes.json();
      
      // Update state with fetched data
      setUsers(userData);
      setInvitations(invitationsData);
      setActivityLogs(activityData);
      setAdditions(additionsData);
      setLoading(false);
    } catch (error) {
      console.error('Error fetching admin data:', error);
      setError('Failed to load admin data. Please try again.');
      setLoading(false);
    }
  };

  // Helper to render active tab content
  const renderTabContent = () => {
    switch (activeTab) {
      case 'users':
        return renderUsers();
      case 'invitations':
        return renderInvitations();
      case 'activity':
        return renderActivityLogs();
      case 'additions':
        return renderAdditions();
      default:
        return <div>Select a tab</div>;
    }
  };

  // Tab content renderers
  const renderUsers = () => {
    return (
      <div className="users-tab">
        <h3 className="text-xl font-bold text-white mb-4">Users Management</h3>
        {loading ? (
          <div className="loading">Loading users...</div>
        ) : error ? (
          <div className="error">{error}</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.map(user => (
                <tr key={user.id}>
                  <td>{user.id}</td>
                  <td>{user.email}</td>
                  <td>{user.role}</td>
                  <td>{new Date(user.created_at).toLocaleString()}</td>
                  <td>
                    <button className="btn-sm btn-danger">Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    );
  };

  const renderInvitations = () => {
    return (
      <div className="invitations-tab">
        <h3 className="text-xl font-bold text-white mb-4">Invitations</h3>
        {loading ? (
          <div className="loading">Loading invitations...</div>
        ) : error ? (
          <div className="error">{error}</div>
        ) : (
          <>
            <button className="btn-primary mb-4">Create New Invitation</button>
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Email</th>
                  <th>Invitation Code</th>
                  <th>Expires</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {invitations.map(invitation => (
                  <tr key={invitation.id}>
                    <td>{invitation.id}</td>
                    <td>{invitation.email}</td>
                    <td>{invitation.code}</td>
                    <td>{new Date(invitation.expires_at).toLocaleString()}</td>
                    <td>
                      <button className="btn-sm btn-danger">Delete</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </>
        )}
      </div>
    );
  };

  const renderActivityLogs = () => {
    return (
      <div className="activity-tab">
        <h3 className="text-xl font-bold text-white mb-4">Activity Logs</h3>
        {loading ? (
          <div className="loading">Loading activity logs...</div>
        ) : error ? (
          <div className="error">{error}</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              {activityLogs.map(log => (
                <tr key={log.id}>
                  <td>{new Date(log.created_at).toLocaleString()}</td>
                  <td>{log.user_email || log.user_id}</td>
                  <td>{log.action}</td>
                  <td>{log.details}</td>
                  <td>{log.ip_address}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    );
  };

  const renderAdditions = () => {
    return (
      <div className="additions-tab">
        <h3 className="text-xl font-bold text-white mb-4">Additions</h3>
        {loading ? (
          <div className="loading">Loading additions...</div>
        ) : error ? (
          <div className="error">{error}</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              {additions.map(addition => (
                <tr key={addition.id}>
                  <td>{new Date(addition.created_at).toLocaleString()}</td>
                  <td>{addition.user_email || addition.user_id}</td>
                  <td>{addition.action}</td>
                  <td>{addition.details}</td>
                  <td>{addition.ip_address}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    );
  };

  return (
    <div className="admin-panel">
      <div className="bg-dark-200 rounded-lg overflow-hidden shadow-lg">
        {/* Tab Navigation */}
        <div className="bg-dark-300 border-b border-dark-400">
          <div className="flex overflow-x-auto">
            <button
              onClick={() => setActiveTab('users')}
              className={`px-4 py-3 font-medium text-sm ${
                activeTab === 'users'
                  ? 'border-b-2 border-primary-500 text-primary-500'
                  : 'text-gray-400 hover:text-white'
              }`}
            >
              Users
            </button>
            <button
              onClick={() => setActiveTab('invitations')}
              className={`px-4 py-3 font-medium text-sm ${
                activeTab === 'invitations'
                  ? 'border-b-2 border-primary-500 text-primary-500'
                  : 'text-gray-400 hover:text-white'
              }`}
            >
              Invitations
            </button>
            <button
              onClick={() => setActiveTab('activity')}
              className={`px-4 py-3 font-medium text-sm ${
                activeTab === 'activity'
                  ? 'border-b-2 border-primary-500 text-primary-500'
                  : 'text-gray-400 hover:text-white'
              }`}
            >
              Activity Logs
            </button>
            <button
              onClick={() => setActiveTab('additions')}
              className={`px-4 py-3 font-medium text-sm ${
                activeTab === 'additions'
                  ? 'border-b-2 border-primary-500 text-primary-500'
                  : 'text-gray-400 hover:text-white'
              }`}
            >
              Additions
            </button>
          </div>
        </div>
        
        {/* Tab Content */}
        <div className="p-6">
          {renderTabContent()}
        </div>
      </div>
    </div>
  );
};

export default AdminPage;