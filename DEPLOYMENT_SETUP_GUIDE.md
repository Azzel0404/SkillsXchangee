# 🚀 SkillsXchangee Deployment Setup Guide

This guide will help you set up the SkillsXchangee application with all necessary dependencies and Pusher configuration for deployment.

## 📋 **Prerequisites**

Before running the deployment script, ensure you have:

- ✅ **PHP 8.0+** installed
- ✅ **Composer** installed
- ✅ **Node.js 16+** and **npm** installed
- ✅ **MySQL** database server running
- ✅ **Web server** (Apache/Nginx) or **XAMPP**

## 🔧 **Quick Setup (Automated)**

### **Option 1: Windows Batch File (Recommended for Windows)**
```bash
# Navigate to backend directory
cd backend

# Run the deployment script
deploy-setup.bat
```

### **Option 2: PowerShell Script**
```powershell
# Navigate to backend directory
cd backend

# Run the PowerShell script
.\deploy-setup.ps1
```

### **Option 3: Bash Script (Linux/Mac)**
```bash
# Navigate to backend directory
cd backend

# Make script executable
chmod +x deploy-setup.sh

# Run the deployment script
./deploy-setup.sh
```

## 📝 **What the Script Does**

The deployment script automatically:

1. **📋 Copies Environment Configuration**
   - Copies `.env.backup` from root to `backend/.env`
   - Adds missing Pusher TLS settings

2. **📦 Installs Dependencies**
   - Installs PHP dependencies via Composer
   - Installs Node.js dependencies via npm

3. **🏗️ Builds Frontend Assets**
   - Compiles CSS and JavaScript
   - Includes Laravel Echo and Pusher configuration

4. **🧹 Clears Laravel Caches**
   - Clears configuration cache
   - Clears route cache
   - Clears view cache

5. **🔑 Generates Application Key**
   - Creates Laravel application key if missing

6. **🧪 Tests Pusher Connection**
   - Verifies Pusher configuration is working

## 🔧 **Manual Setup (If Scripts Don't Work)**

### **Step 1: Environment Configuration**
```bash
# Copy environment file
cp ../.env.backup .env

# Add Pusher TLS settings to .env
echo "" >> .env
echo "# Pusher TLS Settings" >> .env
echo "PUSHER_USE_TLS=false" >> .env
echo "PUSHER_ENCRYPTED=false" >> .env
echo "VITE_PUSHER_FORCE_TLS=false" >> .env
```

### **Step 2: Install Dependencies**
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install
```

### **Step 3: Build Assets**
```bash
# Build frontend assets
npm run build
```

### **Step 4: Clear Caches**
```bash
# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### **Step 5: Generate Application Key**
```bash
# Generate Laravel application key
php artisan key:generate
```

## 🧪 **Testing the Setup**

### **Test Pusher Connection**
```bash
php test-pusher.php
```

Expected output:
```
✅ Pusher test successful!
Message sent to channel: test-channel
Event: test-event
```

### **Test Chat Functionality**
1. Start your web server
2. Navigate to the chat page
3. Open browser developer tools (F12)
4. Check console for: `✅ Pusher connected successfully`
5. Try sending a message

## 🔧 **Pusher Configuration**

The deployment script configures Pusher with these settings:

```env
# Pusher Configuration
PUSHER_APP_ID=2047345
PUSHER_APP_KEY=5c02e54d01ca577ae77e
PUSHER_APP_SECRET=3ad793a15a653af09cd6
PUSHER_APP_CLUSTER=ap1

# TLS Settings (matching your Pusher dashboard)
PUSHER_USE_TLS=false
PUSHER_ENCRYPTED=false
VITE_PUSHER_FORCE_TLS=false
```

## 🚨 **Troubleshooting**

### **Issue: Composer not found**
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### **Issue: npm not found**
```bash
# Install Node.js and npm
# Visit: https://nodejs.org/
```

### **Issue: Pusher connection failed**
1. Check your internet connection
2. Verify Pusher credentials in `.env`
3. Ensure Pusher app settings match configuration

### **Issue: Chat not working**
1. Check browser console for errors
2. Verify frontend assets are built
3. Check Laravel logs: `tail -f storage/logs/laravel.log`

## 📊 **Expected Results**

After successful deployment:

- ✅ **Pusher Connection**: 1+ connections in Pusher dashboard
- ✅ **Message Activity**: Messages appear in Pusher dashboard
- ✅ **Real-time Chat**: Messages appear instantly for both users
- ✅ **No Console Errors**: Clean browser console
- ✅ **Laravel Logs**: Successful message creation and broadcasting

## 🎯 **Next Steps**

1. **Database Setup**: Run `php artisan migrate`
2. **User Registration**: Create test users
3. **Trade Creation**: Create test trades
4. **Chat Testing**: Test real-time messaging
5. **Production Deployment**: Deploy to your hosting provider

## 📞 **Support**

If you encounter issues:

1. Check the troubleshooting section above
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check browser console for JavaScript errors
4. Verify Pusher dashboard for connection status

---

**🎉 Your SkillsXchangee application is now ready for deployment with working real-time chat!**
