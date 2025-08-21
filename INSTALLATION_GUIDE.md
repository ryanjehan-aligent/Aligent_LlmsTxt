# Installation Guide - Aligent LLMs.txt Generator

This guide provides detailed instructions for installing the Aligent_LlmsTxt module using different methods.

## 📋 Prerequisites

Before installing, ensure you have:
- **PHP**: 8.1 or higher
- **Adobe Commerce**: 2.4.4 or higher
- **Git**: For Git-based installation methods
- **Composer**: For Composer-based installation methods
- **File Permissions**: Write access to your Magento installation directory

## 🚀 Installation Methods

### Method 1: Git Clone (Recommended)

This is the simplest method for most installations.

```bash
# Navigate to your Magento root directory
cd /path/to/your/magento

# Clone the module into the correct directory
git clone https://github.com/aligent/magento2-llms-txt.git app/code/Aligent/LlmsTxt

# Enable the module
bin/magento module:enable Aligent_LlmsTxt

# Run setup upgrade
bin/magento setup:upgrade

# Deploy static content (production mode only)
bin/magento setup:static-content:deploy

# Clear cache
bin/magento cache:clean
```

### Method 2: Composer with Git Repository

Use this method if you prefer Composer dependency management.

```bash
# Add the Git repository to Composer
composer config repositories.aligent-llms-txt git https://github.com/aligent/magento2-llms-txt.git

# Install the module
composer require aligent/magento2-llms-txt:dev-main

# Enable the module
bin/magento module:enable Aligent_LlmsTxt

# Run setup upgrade
bin/magento setup:upgrade

# Deploy static content (production mode only)
bin/magento setup:static-content:deploy

# Clear cache
bin/magento cache:clean
```

### Method 3: Local Development with Path Repository

For developers working on the module locally.

1. **Clone the repository to a local directory:**
   ```bash
   git clone https://github.com/aligent/magento2-llms-txt.git /path/to/local/development/aligent-llms-txt
   ```

2. **Add path repository to your Magento's composer.json:**
   ```json
   {
     "repositories": [
       {
         "type": "path",
         "url": "/path/to/local/development/aligent-llms-txt"
       }
     ]
   }
   ```

3. **Install via Composer:**
   ```bash
   composer require aligent/magento2-llms-txt
   ```

4. **Complete the installation:**
   ```bash
   bin/magento module:enable Aligent_LlmsTxt
   bin/magento setup:upgrade
   bin/magento setup:static-content:deploy  # if in production mode
   bin/magento cache:clean
   ```

### Method 4: Manual Installation

For environments without Git or when you prefer manual file management.

1. **Download the module:**
   - Download the ZIP file from the GitHub repository
   - Extract the contents

2. **Copy files to Magento:**
   ```bash
   # Create the directory structure
   mkdir -p app/code/Aligent/LlmsTxt
   
   # Copy all module files to the directory
   cp -r /path/to/extracted/files/* app/code/Aligent/LlmsTxt/
   ```

3. **Complete the installation:**
   ```bash
   bin/magento module:enable Aligent_LlmsTxt
   bin/magento setup:upgrade
   bin/magento setup:static-content:deploy  # if in production mode
   bin/magento cache:clean
   ```

## 🔄 Upgrading

### From Git Clone Installation

```bash
# Navigate to module directory
cd app/code/Aligent/LlmsTxt

# Pull latest changes
git pull origin main

# Run upgrade commands
bin/magento setup:upgrade
bin/magento cache:clean
```

### From Composer Installation

```bash
# Update the module
composer update aligent/magento2-llms-txt

# Run upgrade commands
bin/magento setup:upgrade
bin/magento cache:clean
```

## ✅ Verify Installation

After installation, verify the module is working:

1. **Check module status:**
   ```bash
   bin/magento module:status Aligent_LlmsTxt
   ```
   Should show: `Module is enabled`

2. **Access admin configuration:**
   - Log into Magento Admin
   - Navigate to **Stores > Configuration > Aligent > LLMs.txt Generator**
   - You should see the configuration options

3. **Test file generation:**
   - Enable the module in configuration
   - Add company information
   - Click "Generate Now" button
   - Check if `pub/llms.txt` is created

## 🚨 Troubleshooting

### Common Issues

**Error: "Module not found"**
- Ensure files are in the correct directory: `app/code/Aligent/LlmsTxt/`
- Check file permissions
- Run `bin/magento module:enable Aligent_LlmsTxt`

**Error: "Class not found"**
- Run `bin/magento setup:upgrade`
- Clear generated files: `rm -rf generated/*`
- Run `bin/magento cache:clean`

**Error: "Permission denied"**
- Ensure web server has write permissions to `pub/` directory
- Check Magento file ownership and permissions

**Configuration not showing**
- Clear cache: `bin/magento cache:clean`
- Check ACL permissions for admin user
- Verify module is enabled: `bin/magento module:status`

### File Permissions

Set correct permissions after installation:
```bash
# For files
find app/code/Aligent/LlmsTxt -type f -exec chmod 644 {} \;

# For directories  
find app/code/Aligent/LlmsTxt -type d -exec chmod 755 {} \;

# Ensure pub directory is writable
chmod 755 pub/
```

## 📊 Post-Installation Configuration

1. **Navigate to configuration:**
   Stores > Configuration > Aligent > LLMs.txt Generator

2. **Configure basic settings:**
   - Enable the module
   - Set company name and description
   - Choose which content to include

3. **Set up scheduling:**
   - Choose generation frequency
   - Set specific time if needed

4. **Test generation:**
   - Click "Generate Now"
   - Verify file is created in `pub/llms.txt`

## 🔗 Useful Commands

```bash
# Check module status
bin/magento module:status Aligent_LlmsTxt

# View generated files
ls -la pub/llms*.txt

# Check cron jobs
bin/magento cron:run --group=default

# View logs
tail -f var/log/system.log | grep -i llms
```

## 📞 Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review Magento logs: `var/log/system.log`
3. Create an issue on GitHub: [Issues](https://github.com/aligent/magento2-llms-txt/issues)
4. Include your Magento version, PHP version, and installation method