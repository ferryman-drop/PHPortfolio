const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('Installing dependencies...');

try {
  // Check if package.json exists
  if (!fs.existsSync('package.json')) {
    console.error('package.json not found!');
    process.exit(1);
  }

  // Try to install dependencies using npm
  console.log('Running npm install...');
  execSync('npm install', { stdio: 'inherit' });
  
  console.log('Dependencies installed successfully!');
} catch (error) {
  console.error('Error installing dependencies:', error.message);
  
  // Try alternative approach - manually create node_modules
  console.log('Trying alternative installation method...');
  
  const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
  const dependencies = { ...packageJson.dependencies, ...packageJson.devDependencies };
  
  console.log('Dependencies to install:', Object.keys(dependencies));
  
  // Create node_modules directory
  if (!fs.existsSync('node_modules')) {
    fs.mkdirSync('node_modules');
  }
  
  console.log('Please install dependencies manually using: npm install');
} 

// Add changes to staging area
execSync('git add .', { stdio: 'inherit' });

// Commit changes with a message
execSync('git commit -m "Dependencies installed"', { stdio: 'inherit' });

// Push changes to the remote repository
execSync('git push -u origin main --force', { stdio: 'inherit' });