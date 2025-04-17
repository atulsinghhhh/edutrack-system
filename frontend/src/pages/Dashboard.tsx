import React, { useState, useEffect } from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend,
  LineChart, Line, ResponsiveContainer
} from 'recharts';
import {
  Users, TrendingUp, School, AlertTriangle,
  CheckCircle, Clock, BarChart2, Activity, MapPin, Bell, TrendingDown
} from 'lucide-react';
import { motion } from 'framer-motion';
import TalkToSomeone from '../components/TalkToSomeone';

interface GenderStats {
  gender: string;
  dropout_rate: number;
  total_students: number;
}

interface LocationStats {
  location: string;
  dropout_rate: number;
  total_students: number;
}

interface AnnualPrediction {
  year: number;
  predicted_rate: number;
  confidence_interval: number;
}

interface NewCase {
  id: number;
  student_name: string;
  school: string;
  risk_factors: string[];
  date_flagged: string;
}

interface SchoolTrend {
  school_id: number;
  school_name: string;
  current_rate: number;
  previous_rate: number;
  trend: 'increasing' | 'decreasing' | 'stable';
}

interface UrgentIntervention {
  id: number;
  student_name: string;
  school: string;
  risk_level: number;
  days_since_flagged: number;
  required_actions: string[];
}

interface StatCardProps {
  icon: React.ElementType;
  title: string;
  value: string | number;
  trend: number;
  index: number;
}

const mockData = {
  dropoutTrend: [
    { month: 'Jan', rate: 4.5 },
    { month: 'Feb', rate: 4.2 },
    { month: 'Mar', rate: 4.8 },
    { month: 'Apr', rate: 4.1 },
    { month: 'May', rate: 3.9 },
    { month: 'Jun', rate: 3.7 },
  ],
  regionData: [
    { region: 'North', rate: 5.2 },
    { region: 'South', rate: 4.8 },
    { region: 'East', rate: 3.9 },
    { region: 'West', rate: 4.4 },
  ],
};

const fadeUp = {
  hidden: { opacity: 0, y: 20 },
  visible: (i = 0) => ({
    opacity: 1,
    y: 0,
    transition: {
      delay: i * 0.2,
      duration: 0.5,
    }
  }),
};

const StatCard = ({ icon: Icon, title, value, trend, index }: StatCardProps) => (
  <motion.div
    className="bg-white p-6 rounded-xl shadow-sm"
    custom={index}
    initial="hidden"
    animate="visible"
    variants={fadeUp}
  >
    <div className="flex items-center justify-between">
      <div className="flex items-center space-x-4">
        <div className="p-3 bg-purple-100 rounded-lg">
          <Icon className="h-6 w-6 text-purple-600" />
        </div>
        <div>
          <h3 className="text-sm font-medium text-gray-500">{title}</h3>
          <p className="text-2xl font-semibold text-gray-900">{value}</p>
        </div>
      </div>
      <span className={`text-sm ${trend >= 0 ? 'text-red-500' : 'text-green-500'}`}>
        {trend >= 0 ? `+${trend}%` : `${trend}%`}
      </span>
    </div>
  </motion.div>
);

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#82ca9d'];

const Dashboard = () => {
  const [genderStats, setGenderStats] = useState<GenderStats[]>([]);
  const [locationStats, setLocationStats] = useState<LocationStats[]>([]);
  const [annualPredictions, setAnnualPredictions] = useState<AnnualPrediction[]>([]);
  const [newCases, setNewCases] = useState<NewCase[]>([]);
  const [schoolTrends, setSchoolTrends] = useState<SchoolTrend[]>([]);
  const [urgentInterventions, setUrgentInterventions] = useState<UrgentIntervention[]>([
    {
      id: 1,
      student_name: "John Smith",
      school: "Central High School",
      risk_level: 85,
      days_since_flagged: 5,
      required_actions: ["Counseling", "Parent Meeting", "Academic Support"]
    },
    {
      id: 2,
      student_name: "Maria Garcia",
      school: "Westside Academy",
      risk_level: 75,
      days_since_flagged: 2,
      required_actions: ["Academic Support", "Mentorship"]
    },
    {
      id: 3,
      student_name: "David Chen",
      school: "East Valley High",
      risk_level: 65,
      days_since_flagged: 8,
      required_actions: ["Parent Meeting", "Attendance Monitoring"]
    },
    {
      id: 4,
      student_name: "Sarah Johnson",
      school: "Northside Prep",
      risk_level: 90,
      days_since_flagged: 1,
      required_actions: ["Emergency Counseling", "Parent Meeting", "Academic Support"]
    },
    {
      id: 5,
      student_name: "Michael Brown",
      school: "South High School",
      risk_level: 70,
      days_since_flagged: 4,
      required_actions: ["Academic Support", "Attendance Monitoring"]
    }
  ]);
  const [sortConfig, setSortConfig] = useState<{ key: 'risk_level' | 'days_since_flagged' | null; direction: 'asc' | 'desc' }>({
    key: null,
    direction: 'asc'
  });

  const handleSort = (key: 'risk_level' | 'days_since_flagged') => {
    setSortConfig(prevConfig => ({
      key,
      direction: prevConfig.key === key && prevConfig.direction === 'asc' ? 'desc' : 'asc'
    }));
  };

  const sortedInterventions = React.useMemo(() => {
    if (!sortConfig.key) return urgentInterventions;

    return [...urgentInterventions].sort((a, b) => {
      const aValue = a[sortConfig.key as 'risk_level' | 'days_since_flagged'];
      const bValue = b[sortConfig.key as 'risk_level' | 'days_since_flagged'];
      
      if (sortConfig.direction === 'asc') {
        return aValue - bValue;
      }
      return bValue - aValue;
    });
  }, [urgentInterventions, sortConfig]);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const response = await fetch('http://localhost/DropoutStud/backend/api/dashboard.php');
        const data = await response.json();
        console.log('Dashboard data:', data);
        
        setGenderStats(data.gender_stats);
        setLocationStats(data.location_stats);
        setAnnualPredictions(data.annual_predictions);
        setNewCases(data.new_cases);
        setSchoolTrends(data.school_trends);
        setUrgentInterventions(data.urgent_interventions);
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    };

    fetchDashboardData();
  }, []);

  return (
    <div className="space-y-8">
      <motion.div
        initial="hidden"
        animate="visible"
        variants={fadeUp}
      >
        <h1 className="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
        <p className="text-gray-500">Monitor dropout statistics and trends</p>
      </motion.div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard icon={Users} title="Total Students" value="12,345" trend={-2.5} index={0} />
        <StatCard icon={TrendingUp} title="Dropout Rate" value="4.2%" trend={1.2} index={1} />
        <StatCard icon={School} title="Total Schools" value="48" trend={0} index={2} />
        <StatCard icon={AlertTriangle} title="At Risk" value="234" trend={5.8} index={3} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <motion.div
          className="bg-white p-6 rounded-xl shadow-sm"
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true }}
          variants={fadeUp}
        >
          <h2 className="text-lg font-semibold mb-4">Dropout Rate Trend</h2>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={mockData.dropoutTrend}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="month" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Line type="monotone" dataKey="rate" stroke="#9333ea" />
            </LineChart>
          </ResponsiveContainer>
        </motion.div>

        <motion.div
          className="bg-white p-6 rounded-xl shadow-sm"
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true }}
          variants={fadeUp}
        >
          <h2 className="text-lg font-semibold mb-4">Dropout Rate by Region</h2>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={mockData.regionData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="region" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Bar dataKey="rate" fill="#9333ea" />
            </BarChart>
          </ResponsiveContainer>
        </motion.div>
      </div>

      {/* Talk to Someone Section */}
      <div className="bg-white rounded-xl shadow-lg p-6">
        <TalkToSomeone />
      </div>

      {/* New Dropout Cases */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 1.0 }}
        className="bg-white p-6 rounded-xl shadow-sm"
      >
        <h2 className="text-lg font-semibold mb-4">New Dropout Cases</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Factors</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Flagged</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {newCases.map((case_) => (
                <tr key={case_.id}>
                  <td className="px-6 py-4 whitespace-nowrap">{case_.student_name}</td>
                  <td className="px-6 py-4 whitespace-nowrap">{case_.school}</td>
                  <td className="px-6 py-4">
                    <div className="flex flex-wrap gap-1">
                      {case_.risk_factors.map((factor, index) => (
                        <span key={index} className="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                          {factor}
                        </span>
                      ))}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">{case_.date_flagged}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </motion.div>

      {/* Schools with Increasing Trend */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 1.1 }}
        className="bg-white p-6 rounded-xl shadow-sm"
      >
        <h2 className="text-lg font-semibold mb-4">Schools with Increasing Trend</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Rate</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Previous Rate</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {schoolTrends.map((school) => (
                <tr key={school.school_id}>
                  <td className="px-6 py-4 whitespace-nowrap">{school.school_name}</td>
                  <td className="px-6 py-4 whitespace-nowrap">{school.current_rate}%</td>
                  <td className="px-6 py-4 whitespace-nowrap">{school.previous_rate}%</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                      school.trend === 'increasing' 
                        ? 'bg-red-100 text-red-800' 
                        : school.trend === 'decreasing'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800'
                    }`}>
                      {school.trend}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </motion.div>

      {/* Urgent Interventions Required */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 1.2 }}
        className="bg-white p-6 rounded-xl shadow-sm"
      >
        <h2 className="text-lg font-semibold mb-4">Urgent Interventions Required</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                <th 
                  className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                  onClick={() => handleSort('risk_level')}
                >
                  <div className="flex items-center">
                    Risk Level
                    {sortConfig.key === 'risk_level' && (
                      <span className="ml-1">
                        {sortConfig.direction === 'asc' ? '↑' : '↓'}
                      </span>
                    )}
                  </div>
                </th>
                <th 
                  className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                  onClick={() => handleSort('days_since_flagged')}
                >
                  <div className="flex items-center">
                    Days Flagged
                    {sortConfig.key === 'days_since_flagged' && (
                      <span className="ml-1">
                        {sortConfig.direction === 'asc' ? '↑' : '↓'}
                      </span>
                    )}
                  </div>
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {sortedInterventions.map((intervention) => (
                <tr key={intervention.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{intervention.student_name}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-gray-500">{intervention.school}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-3 py-1 text-xs font-semibold rounded-full ${
                      intervention.risk_level > 80 
                        ? 'bg-red-100 text-red-800' 
                        : intervention.risk_level > 60
                        ? 'bg-orange-100 text-orange-800'
                        : 'bg-yellow-100 text-yellow-800'
                    }`}>
                      {intervention.risk_level}%
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-3 py-1 text-xs font-semibold rounded-full ${
                      intervention.days_since_flagged > 7 
                        ? 'bg-red-100 text-red-800' 
                        : intervention.days_since_flagged > 3
                        ? 'bg-orange-100 text-orange-800'
                        : 'bg-yellow-100 text-yellow-800'
                    }`}>
                      {intervention.days_since_flagged} days
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex flex-wrap gap-1">
                      {intervention.required_actions.map((action, index) => (
                        <span 
                          key={index} 
                          className="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors"
                        >
                          {action}
                        </span>
                      ))}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </motion.div>
    </div>
  );
};

export default Dashboard;
