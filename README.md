# Magento 2 LogManager Module

## Overview

The **LogManager** module for Magento 2 provides an easy way to view and manage logs directly from the Magento admin panel. This module enhances the ability to monitor and troubleshoot issues without needing to access log files directly via the server.

## Features

- View Magento log files from the admin panel
- Delete log files directly from the admin interface
- Filter log content by search keywords
- Paginate log entries for easier navigation

## Installation

### 1. Install via `app/code` Directory

1. Clone the repository:

    ```bash
    git clone https://github.com/AlexTs556/magento2-logmanager.git
    ```

2. Copy the module to your Magento installation:

    ```bash
    cp -R magento2-logmanager/ <Magento_Root>/app/code/ProDevTools/LogManager/
    ```

3. Enable the module

    ```bash
    php bin/magento module:enable ProDevTools_LogManager
    php bin/magento setup:upgrade
    php bin/magento cache:clean
    ```

### 2. Install via Composer

1. Add the repository to your `composer.json`:

    ```bash
    composer require prodevtools/magento2-logmanager
    ```

2. Enable the module:

    ```bash
    php bin/magento module:enable ProDevTools_LogManager
    php bin/magento setup:upgrade
    php bin/magento cache:clean
    ```

## Usage

Once installed, you can access the log management features in the Magento admin panel under the **System** menu. Here you can view, filter, and delete log files.

## Support

If you encounter any issues, feel free to open an issue on the [GitHub repository](https://github.com/AlexTs556/magento2-logmanager/issues).
