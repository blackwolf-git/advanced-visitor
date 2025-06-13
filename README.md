# Advanced Visitor Tracking System

## Overview
The Advanced Visitor Tracking System is a comprehensive solution for monitoring and analyzing website visitors with detailed insights into their behavior, device information, and interaction patterns. This self-hosted system provides privacy-focused analytics without relying on third-party services.

## Key Features

- **Visitor Fingerprinting**: Unique identification using device characteristics
- **Detailed Analytics**: 
  - Device information (hardware, screen, browser)
  - Network data (IP, location, ISP)
  - Performance metrics (page load times)
- **Interaction Tracking**:
  - Mouse movements
  - Clicks
  - Scrolls
  - Key presses (non-sensitive)
- **Session Analysis**:
  - Active/inactive time
  - Page engagement
- **Dashboard**:
  - Visual statistics
  - Visitor details
  - Geographic distribution

## Technology Stack

- **Frontend**: JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+/MariaDB
- **Dependencies**:
  - Chart.js for data visualization
  - Bootstrap RTL for Arabic interface

## Installation

### Requirements
- Web server (Apache/Nginx)
- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB
- Modern browser with JavaScript support

### Setup Instructions

1. **Database Setup**:
   ```sql
   CREATE DATABASE visitor_tracking;
   USE visitor_tracking;
   -- Run all SQL statements from the "5. SQL لإنشاء جداول قاعدة البيانات" section
   ```

2. **Configuration**:
   Edit `db_config.php` with your database credentials:
   ```php
   $db_host = 'localhost';
   $db_user = 'your_username';
   $db_pass = 'your_password';
   $db_name = 'visitor_tracking';
   ```

3. **File Deployment**:
   Upload all PHP files to your web server and place `visitor_tracker.js` in your website's public JavaScript directory.

4. **Integration**:
   Add the tracking script to your website:
   ```html
   <script src="/path/to/visitor_tracker.js"></script>
   ```

## Usage

### Tracking Implementation
Simply include the JavaScript file on any page you want to track. Data collection begins automatically on page load.

### Accessing the Dashboard
1. Navigate to `dashboard.php` on your server
2. Login with admin credentials (set in `auth_check.php`)

### Dashboard Features

1. **Overview Metrics**:
   - Total visits
   - Unique visitors
   - Returning visitors
   - Average session duration

2. **Visual Analytics**:
   - Device type distribution (pie chart)
   - Top countries (bar chart)

3. **Visitor Details**:
   - Click any visitor to see:
     - Device specifications
     - Location information
     - Interaction statistics
     - Session history

## Privacy Considerations

This system is designed with privacy in mind:
- No personal information is collected
- IP addresses are stored but not displayed in full
- Key presses are tracked without recording content
- All data is stored on your own server

## Customization Options

1. **Data Collection**:
   - Modify `visitor_tracker.js` to add/remove collected metrics
   - Adjust tracking intervals in the JavaScript

2. **Dashboard**:
   - Add new charts by editing `dashboard.php`
   - Customize the Arabic RTL interface in the HTML

3. **Security**:
   - Enhance authentication in `auth_check.php`
   - Implement IP restrictions for admin access

## Performance Notes

- The system uses efficient data collection methods:
  - Beacon API for reliable data transmission
  - Asynchronous network requests
  - Optimized database structure

- For high-traffic sites:
  - Consider adding database indexes
  - Implement caching for dashboard statistics
  - Schedule regular data archiving

## Screenshots (Conceptual)

1. **Main Dashboard**: Showing overview metrics and charts
2. **Visitor Details**: Displaying device fingerprint and session history
3. **Geographic View**: Map visualization of visitor locations

## Troubleshooting

Common issues and solutions:

1. **No data appearing**:
   - Verify JavaScript is loading (check browser console)
   - Confirm PHP scripts are accessible and returning 200 status
   - Check database permissions

2. **Performance problems**:
   - Optimize MySQL configuration
   - Add indexes to frequently queried columns
   - Limit historical data retention

3. **Login issues**:
   - Verify credentials in `auth_check.php`
   - Check session configuration on your server

## License

This project is open-source and available for modification and distribution under the MIT License.

## Contribution

While this is currently a solo project by Ebrahim Gen, contributions are welcome. Please fork the repository and submit pull requests for consideration.

## Roadmap

Planned future enhancements:
- Real-time visitor monitoring
- Enhanced bot detection
- Export capabilities (CSV/JSON)
- API for data access
- Multi-user support with permissions

---

This professional README provides comprehensive documentation for showcasing the project in your portfolio. It highlights the system's capabilities while giving clear instructions for implementation and customization.
