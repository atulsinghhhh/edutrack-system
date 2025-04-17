import React, { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { ArrowRight, BarChart2, Users, Upload, BookOpen } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

const fadeUp = {
  hidden: { opacity: 0, y: 30 },
  visible: (i = 0) => ({
    opacity: 1,
    y: 0,
    transition: {
      delay: i * 0.2,
      duration: 0.6,
      ease: 'easeOut'
    }
  })
};

const Home = () => {
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();

  useEffect(() => {
    // If user is authenticated, redirect to dashboard
    if (isAuthenticated) {
      navigate('/dashboard');
    }
  }, [isAuthenticated, navigate]);

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-100 via-white to-purple-200">
      
      {/* Hero Section */}
      <div className="bg-transparent">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
          <motion.div
            className="text-center"
            initial="hidden"
            animate="visible"
            variants={fadeUp}
          >
            <h1 className="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
              <span className="block">Student Dropout</span>
              <span className="block text-purple-600">Prediction System</span>
            </h1>
            <motion.p
              className="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl"
              variants={fadeUp}
              custom={1}
            >
              Identify at-risk students early and implement effective interventions to improve retention rates.
            </motion.p>
            <motion.div
              className="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8"
              variants={fadeUp}
              custom={2}
            >
              <div className="rounded-md shadow">
                <Link
                  to="/login"
                  className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 md:py-4 md:text-lg md:px-10"
                >
                  Get Started
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Link>
              </div>
            </motion.div>
          </motion.div>
        </div>
      </div>

      {/* Features Section */}
      <div className="py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="lg:text-center">
            <h2 className="text-base text-purple-600 font-semibold tracking-wide uppercase">Features</h2>
            <p className="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
              Everything you need to prevent student dropout
            </p>
          </div>

          <div className="mt-10 grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
            {[{
              Icon: BarChart2,
              title: "Data Analysis",
              desc: "Advanced analytics to identify patterns and predict potential dropouts."
            }, {
              Icon: Users,
              title: "Student Monitoring",
              desc: "Track student performance and engagement metrics in real-time."
            }, {
              Icon: Upload,
              title: "Data Upload",
              desc: "Easily upload and process student data from various sources."
            }, {
              Icon: BookOpen,
              title: "Intervention Plans",
              desc: "Access proven intervention strategies to support at-risk students."
            }].map(({ Icon, title, desc }, index) => (
              <motion.div
                key={title}
                className="relative"
                custom={index}
                initial="hidden"
                animate="visible"
                variants={fadeUp}
              >
                <div className="absolute flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                  <Icon className="h-6 w-6" />
                </div>
                <div className="ml-16">
                  <h3 className="text-lg leading-6 font-medium text-gray-900">{title}</h3>
                  <p className="mt-2 text-base text-gray-500">{desc}</p>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* How it Works Section */}
      <div className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h2 className="text-base text-purple-600 font-semibold tracking-wide uppercase">Process</h2>
            <p className="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
              How it Works
            </p>
          </div>

          <div className="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {[
              {
                number: "1",
                title: "Data Collection",
                description: "Upload student data including academic performance, attendance records, and demographic information."
              },
              {
                number: "2",
                title: "Analysis & Prediction",
                description: "Our AI system analyzes the data to identify patterns and predict potential dropout risks."
              },
              {
                number: "3",
                title: "Intervention",
                description: "Receive actionable insights and recommended interventions to support at-risk students."
              }
            ].map((step, index) => (
              <motion.div
                key={step.number}
                className="bg-purple-50 rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                viewport={{ once: true }}
              >
                <div className="flex items-center">
                  <div className="flex-shrink-0 w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center">
                    <span className="text-white text-xl font-bold">{step.number}</span>
                  </div>
                  <h3 className="ml-4 text-xl font-semibold text-gray-900">{step.title}</h3>
                </div>
                <p className="mt-4 text-gray-600">{step.description}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* Testimonials Section */}
      <div className="py-16 bg-gradient-to-br from-purple-50 to-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h2 className="text-base text-purple-600 font-semibold tracking-wide uppercase">Testimonials</h2>
            <p className="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
              What Our Users Say
            </p>
          </div>

          <div className="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {[
              {
                quote: "This system has revolutionized how we identify and support at-risk students. The predictions are incredibly accurate and have helped us improve our retention rates significantly.",
                name: "Dr. Sarah Johnson",
                designation: "Dean of Students, University of Technology"
              },
              {
                quote: "The intervention recommendations are spot-on. We've seen a 40% reduction in dropout rates since implementing this system. It's become an essential tool for our academic success team.",
                name: "Michael Chen",
                designation: "Academic Advisor, State College"
              },
              {
                quote: "The user interface is intuitive and the data visualization tools are powerful. It's made our job much easier in tracking student progress and implementing timely interventions.",
                name: "Lisa Rodriguez",
                designation: "Student Success Coordinator, Community College"
              }
            ].map((testimonial, index) => (
              <motion.div
                key={index}
                className="bg-white rounded-xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                viewport={{ once: true }}
              >
                <div className="relative">
                  <svg
                    className="absolute -top-4 -left-4 h-8 w-8 text-purple-200"
                    fill="currentColor"
                    viewBox="0 0 32 32"
                  >
                    <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" />
                  </svg>
                  <p className="text-gray-600 text-lg leading-relaxed">
                    {testimonial.quote}
                  </p>
                </div>
                <div className="mt-6">
                  <p className="text-gray-900 font-semibold">{testimonial.name}</p>
                  <p className="text-purple-600 text-sm">{testimonial.designation}</p>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* CTA Section */}
      <motion.div
        className="bg-purple-100"
        initial={{ opacity: 0, y: 40 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.7 }}
        viewport={{ once: true }}
      >
        <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
          <h2 className="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            <span className="block">Ready to improve student retention?</span>
            <span className="block text-purple-600">Start using our system today.</span>
          </h2>
          <div className="mt-8 flex lg:mt-0 lg:flex-shrink-0">
            <div className="inline-flex rounded-md shadow">
              <Link
                to="/signup"
                className="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700"
              >
                Sign up for free
              </Link>
            </div>
            <div className="ml-3 inline-flex rounded-md shadow">
              <Link
                to="/contact"
                className="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-purple-600 bg-white hover:bg-purple-50"
              >
                Contact us
              </Link>
            </div>
          </div>
        </div>
      </motion.div>
    </div>
  );
};

export default Home;
