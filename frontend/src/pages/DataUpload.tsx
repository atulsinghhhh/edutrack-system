import React, { useState } from 'react';
import { Upload, CheckCircle, XCircle } from 'lucide-react';

const DataUpload = () => {
  const [dragActive, setDragActive] = useState(false);
  const [uploadStatus, setUploadStatus] = useState(null);

  const handleDrag = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === "dragenter" || e.type === "dragover") {
      setDragActive(true);
    } else if (e.type === "dragleave") {
      setDragActive(false);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    
    const files = e.dataTransfer.files;
    handleFiles(files);
  };

  const handleFileInput = (e) => {
    const files = e.target.files;
    handleFiles(files);
  };

  const handleFiles = (files) => {
    // Simulate file upload
    setUploadStatus('uploading');
    setTimeout(() => {
      if (files[0]?.name.endsWith('.csv') || files[0]?.name.endsWith('.xlsx')) {
        setUploadStatus('success');
      } else {
        setUploadStatus('error');
      }
    }, 2000);
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Data Upload</h1>
        <p className="text-gray-500">Upload student data for analysis</p>
      </div>

      <div className="bg-white p-8 rounded-xl shadow-sm">
        <div
          className={`relative border-2 border-dashed rounded-lg p-8 text-center ${
            dragActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300'
          }`}
          onDragEnter={handleDrag}
          onDragLeave={handleDrag}
          onDragOver={handleDrag}
          onDrop={handleDrop}
        >
          <input
            type="file"
            className="hidden"
            id="file-upload"
            accept=".csv,.xlsx"
            onChange={handleFileInput}
          />
          
          <div className="space-y-4">
            <div className="flex justify-center">
              <Upload className="h-12 w-12 text-gray-400" />
            </div>
            <div>
              <p className="text-lg font-medium text-gray-900">
                Drag and drop your files here
              </p>
              <p className="text-sm text-gray-500">
                or
              </p>
              <label
                htmlFor="file-upload"
                className="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer"
              >
                Browse Files
              </label>
            </div>
            <p className="text-xs text-gray-500">
              Supported formats: CSV, Excel (xlsx)
            </p>
          </div>
        </div>

        {uploadStatus && (
          <div className="mt-6">
            <div className="flex items-center space-x-3">
              {uploadStatus === 'uploading' && (
                <>
                  <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                  <span className="text-gray-600">Uploading file...</span>
                </>
              )}
              {uploadStatus === 'success' && (
                <>
                  <CheckCircle className="h-5 w-5 text-green-500" />
                  <span className="text-green-600">File uploaded successfully!</span>
                </>
              )}
              {uploadStatus === 'error' && (
                <>
                  <XCircle className="h-5 w-5 text-red-500" />
                  <span className="text-red-600">Invalid file format. Please upload CSV or Excel files only.</span>
                </>
              )}
            </div>
          </div>
        )}
      </div>

      <div className="bg-white p-6 rounded-xl shadow-sm">
        <h2 className="text-lg font-semibold mb-4">Upload Guidelines</h2>
        <div className="space-y-4">
          <div>
            <h3 className="text-md font-medium text-gray-900">Required Columns</h3>
            <ul className="mt-2 list-disc list-inside text-gray-500">
              <li>Student ID (unique identifier)</li>
              <li>Name (first and last name)</li>
              <li>Grade Level</li>
              <li>Attendance Rate</li>
              <li>GPA</li>
              <li>Demographic Information</li>
            </ul>
          </div>
          <div>
            <h3 className="text-md font-medium text-gray-900">File Format</h3>
            <ul className="mt-2 list-disc list-inside text-gray-500">
              <li>CSV files should use comma as delimiter</li>
              <li>Excel files should have data in the first sheet</li>
              <li>First row should contain column headers</li>
              <li>Maximum file size: 10MB</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DataUpload;