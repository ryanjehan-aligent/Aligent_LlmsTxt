# Aligent_LlmsTxt Module for Adobe Commerce

## Overview

The Aligent_LlmsTxt module generates store-specific llms.txt files to make your Adobe Commerce website content accessible to AI assistants like ChatGPT, Claude, and Gemini. The module allows administrators to configure which content entities are included and schedule automatic generation.

## Features

- **Store-Specific Generation**: Generate separate llms.txt files for each store, website, or globally
- **Configurable Content Entities**: Choose which entities to include:
  - CMS Pages
  - Products (with configurable limit)
  - Categories
- **Company Information**: Add custom company name, description, and additional information
- **Flexible Scheduling**: Set automatic generation frequency:
  - Daily (with specific time)
  - Weekly (with specific time)
  - Monthly (with specific time)
  - Yearly (with specific time)
  - Never (manual only)
- **Manual Generation**: "Generate Now" button in admin for immediate file creation
- **Status Monitoring**: View file existence, size, and last generation timestamp

## Installation

### Via Composer (Recommended)

1. Install the module via Composer:
   ```bash
   composer require aligent/magento2-llms-txt
   ```

2. Enable the module:
   ```bash
   bin/magento module:enable Aligent_LlmsTxt
   ```

3. Run setup upgrade:
   ```bash
   bin/magento setup:upgrade
   ```

4. Deploy static content (if in production mode):
   ```bash
   bin/magento setup:static-content:deploy
   ```

5. Clear cache:
   ```bash
   bin/magento cache:clean
   ```

### Manual Installation

1. Copy the module to your Adobe Commerce installation:
   ```bash
   cp -r app/code/Aligent/LlmsTxt /path/to/magento/app/code/Aligent/
   ```

2. Enable the module:
   ```bash
   bin/magento module:enable Aligent_LlmsTxt
   ```

3. Run setup upgrade:
   ```bash
   bin/magento setup:upgrade
   ```

4. Deploy static content (if in production mode):
   ```bash
   bin/magento setup:static-content:deploy
   ```

5. Clear cache:
   ```bash
   bin/magento cache:clean
   ```

## Requirements

- PHP 8.1 or higher
- Adobe Commerce / Magento 2.4.x or higher
- Composer (for Composer installation method)

## Configuration

Navigate to **Stores > Configuration > Aligent > LLMs.txt Generator** in the admin panel.

### General Settings
- **Enable**: Enable/disable the module
- **Company Name**: Your company name (appears in the file)
- **Company Description**: Brief description of your company
- **Extra Information**: Additional information to include

### Entity Selection
- **Include CMS Pages**: Include CMS page content
- **Include Products**: Include product information
- **Include Categories**: Include category structure
- **Product Limit**: Maximum number of products to include (default: 100)

### Generation Schedule
- **Generation Frequency**: How often to automatically generate the file
- **Generation Time**: Specific time for daily/weekly/monthly/yearly generation (HH:MM:SS format)

### File Status
- View current file status (exists/not exists)
- View file size
- View last generation timestamp
- **Generate Now** button for manual generation

## Generated File Locations

Files are generated in the `pub` directory with the following naming convention:

- **Global/Default**: `pub/llms.txt`
- **Website-specific**: `pub/llms_website_{id}.txt`
- **Store-specific**: `pub/llms_store_{id}.txt`

## File Format

The generated llms.txt file contains:

```
# LLMs.txt

This file contains structured information about our website to help AI assistants understand our content.

## Company: [Your Company Name]

### About Us
[Your company description]

### Additional Information
[Your extra information]

## CMS Pages
### [Page Title]
URL: [Page URL]
Description: [Meta description]
Content: [Truncated page content]

## Products
### [Product Name]
SKU: [Product SKU]
URL: [Product URL]
Price: $[Price]
In Stock: Yes/No
Description: [Product description]

## Categories
### [Category Name]
Path: [Parent > Child > Category]
URL: [Category URL]
Product Count: [Number]
Description: [Category description]

---
Generated: [Timestamp] GMT
```

## Testing Instructions

### 1. Basic Functionality Test
1. Enable the module in configuration
2. Fill in company information
3. Enable all entity types
4. Click "Generate Now"
5. Check if file exists at `pub/llms.txt`

### 2. Multi-Store Testing
1. Create multiple store views
2. Configure different settings per store
3. Generate files for each store
4. Verify separate files are created:
   - `pub/llms_store_1.txt`
   - `pub/llms_store_2.txt`

### 3. Scheduling Test
1. Set frequency to "Daily"
2. Wait for cron to run (may need to wait until the next day)
3. Check if file is regenerated automatically
4. Verify last generated timestamp updates

### 4. Content Filtering Test
1. Disable specific entity types
2. Regenerate the file
3. Verify only enabled entities appear in the file

### 5. Product Limit Test
1. Set product limit to 10
2. Regenerate the file
3. Verify only 10 products are included

## Cron Job

The module runs via Magento's cron system. The job runs every hour and checks each store's configuration to determine if generation is needed based on the frequency setting.

To run cron manually:
```bash
bin/magento cron:run --group=default
```

## Troubleshooting

### File Not Generating
1. Check if module is enabled in configuration
2. Verify write permissions on `pub` directory
3. Check system.log for errors
4. Ensure cron is running properly

### Missing Content
1. Verify entities are enabled in configuration
2. Check that content exists and is active
3. For store-specific generation, ensure content is assigned to the correct store

### Performance Issues
1. Reduce product limit if generation is slow
2. Consider generating during off-peak hours
3. Monitor server resources during generation

## Module Structure

```
app/code/Aligent/LlmsTxt/
├── Block/
│   └── Adminhtml/
│       └── System/
│           └── Config/
│               ├── FileStatus.php
│               ├── GenerateButton.php
│               └── LastGenerated.php
├── Controller/
│   └── Adminhtml/
│       └── System/
│           └── Config/
│               └── Generate.php
├── Cron/
│   └── Generate.php
├── Model/
│   ├── Config/
│   │   └── Source/
│   │       └── Frequency.php
│   ├── DataProvider/
│   │   ├── CategoryProvider.php
│   │   ├── CmsPageProvider.php
│   │   └── ProductProvider.php
│   └── Generator.php
├── etc/
│   ├── acl.xml
│   ├── crontab.xml
│   ├── module.xml
│   └── adminhtml/
│       ├── routes.xml
│       └── system.xml
├── view/
│   └── adminhtml/
│       └── templates/
│           └── system/
│               └── config/
│                   └── generate_button.phtml
├── registration.php
└── README.md
```

## Support

For issues and feature requests, please use the GitHub issue tracker.

## License

Copyright Aligent All rights reserved.

See COPYING.txt for license details.
