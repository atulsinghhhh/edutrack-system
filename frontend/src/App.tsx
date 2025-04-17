import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import Navbar from './components/Navbar';
import Sidebar from './components/Sidebar';
import Footer from './components/Footer';
import Chatbot from './components/Chatbot';
import Home from './pages/Home';
import Dashboard from './pages/Dashboard';
import DropoutFactors from './pages/DropoutFactors';
import Interventions from './pages/Interventions';
import DataUpload from './pages/DataUpload';
import Contact from './pages/Contact';
import Login from './pages/Login';
import SignUp from './pages/SignUp';

// Protected Route component
const ProtectedRoute = ({ children }: { children: React.ReactNode }) => {
  const { isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return (
    <div className="flex">
      <Sidebar />
      <main className="flex-1 p-8">
        {children}
        <div className="fixed bottom-4 right-4 w-96 h-[600px] z-50">
          <Chatbot />
        </div>
      </main>
    </div>
  );
};

function App() {
  return (
    <AuthProvider>
      <Router>
        <div className="min-h-screen bg-gray-50 flex flex-col">
          <Navbar />
          <div className="flex-1">
            <Routes>
              {/* Public Routes */}
              <Route path="/" element={<Home />} />
              <Route path="/login" element={<Login />} />
              <Route path="/signup" element={<SignUp />} />
              <Route path="/contact" element={<Contact />} />

              {/* Protected Routes */}
              <Route
                path="/dashboard"
                element={
                  <ProtectedRoute>
                    <Dashboard />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/factors"
                element={
                  <ProtectedRoute>
                    <DropoutFactors />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/interventions"
                element={
                  <ProtectedRoute>
                    <Interventions />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/data-upload"
                element={
                  <ProtectedRoute>
                    <DataUpload />
                  </ProtectedRoute>
                }
              />
            </Routes>
          </div>
          <Footer />
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;