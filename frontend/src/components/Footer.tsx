import React from 'react';
import { Link } from 'react-router-dom';

const Footer: React.FC = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-gray-900 text-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
          {/* About Section */}
          <div>
            <h3 className="text-2xl font-bold mb-4">edutrack</h3>
            <p className="text-gray-400 mb-4">
              Empowering students to make informed decisions about their educational journey by analyzing dropout trends and offering actionable insights.
            </p>
            <div className="flex space-x-4">
              <a href="#" aria-label="Twitter" className="text-gray-400 hover:text-white">
                {/* Twitter SVG */}
                <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">...</svg>
              </a>
              <a href="#" aria-label="Instagram" className="text-gray-400 hover:text-white">
                {/* Instagram SVG */}
                <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">...</svg>
              </a>
              <a href="#" aria-label="LinkedIn" className="text-gray-400 hover:text-white">
                {/* LinkedIn SVG */}
                <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">...</svg>
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-xl font-bold mb-4">Quick Links</h3>
            <ul className="space-y-2 text-gray-400">
              <li><Link to="/" className="hover:text-white">Home</Link></li>
              <li><Link to="/about" className="hover:text-white">About Us</Link></li>
              <li><Link to="/contact" className="hover:text-white">Contact</Link></li>
              <li><Link to="/privacy" className="hover:text-white">Privacy Policy</Link></li>
              <li><Link to="/dashboard" className="hover:text-white">Dashboard</Link></li>
            </ul>
          </div>

          {/* Services */}
          <div>
            <h3 className="text-xl font-bold mb-4">Services</h3>
            <ul className="space-y-2 text-gray-400">
              <li>Student Tracking</li>
              <li>Dropout Prediction</li>
              <li>Regional Reports</li>
              <li>Awareness Campaigns</li>
            </ul>
          </div>

          {/* Newsletter */}
          <div>
            <h3 className="text-xl font-bold mb-4">Subscribe to our Newsletter</h3>
            <p className="text-gray-400 mb-4">Stay updated with our latest insights and analytics.</p>
            <form className="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
              <input
                type="email"
                placeholder="Enter your email"
                className="w-full px-3 py-2 rounded-md text-black focus:outline-none"
              />
              <button
                type="submit"
                className="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md text-white font-medium"
              >
                Subscribe
              </button>
            </form>
          </div>
        </div>

        {/* Footer Bottom */}
        <div className="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500 text-sm">
          <p>&copy; {currentYear} edutrack. All rights reserved.</p>
          <p>Made with by Team edutrack</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
