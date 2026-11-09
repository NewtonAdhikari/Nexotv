# NexoTV – Live TV Streaming Platform

<p align="center">
  <strong>A Web-Based IPTV Platform for Accessing and Streaming Live Television Channels</strong>
</p>

<p align="center">
  <a href="https://sirc.liveblog365.com/?i=1">
    <img src="https://img.shields.io/badge/LIVE-DEMO-brightgreen?style=for-the-badge" alt="Live Demo">
  </a>
  <img src="https://img.shields.io/badge/PHP-Backend-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/JavaScript-Frontend-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
</p>

---

## Live Demonstration

<https://sirc.liveblog365.com/?i=1>

Explore NexoTV to browse available television channels and experience live TV streaming directly through a web browser.

## Overview

NexoTV is a web-based live television streaming platform developed using PHP and MySQL. It provides a centralized interface where users can access different television channels through externally sourced streaming links.

The platform combines server-side PHP processing, MySQL database management, and browser-based video playback to provide a convenient online television streaming experience.

Users can select available channels and watch supported live streams without installing a separate desktop or mobile application.

## Key Features

- **Live TV Streaming:** Watch available television streams directly through a web browser.
- **Multiple TV Channels:** Access different television channels from one centralized platform.
- **Channel Management:** Store and manage channel information using MySQL.
- **Dynamic Streaming Links:** Load external streaming URLs for channel playback.
- **Integrated Video Player:** Play supported streaming formats within the website.
- **PHP Backend:** Handle server-side application functionality and database communication.
- **MySQL Database:** Store channel information and related application data.
- **Browser Accessibility:** Access the platform through supported desktop and mobile browsers.
- **Centralized Interface:** Browse and access available television streams from one location.

## Technology Stack

| Technology | Purpose |
|---|---|
| PHP | Backend development and server-side processing |
| MySQL | Database management |
| HTML5 | Website structure |
| CSS3 | Styling and responsive interface |
| JavaScript | Client-side interactions and playback functionality |
| Video Player | Browser-based streaming playback |
| External Stream URLs | Television channel streaming sources |

## System Architecture

```text
                 USER
                   |
                   v
          NexoTV Web Interface
              (HTML/CSS)
                   |
                   v
          JavaScript Interactions
                   |
                   v
              PHP Backend
                   |
                   v
             MySQL Database
                   |
                   v
          Channel Information
                   |
                   v
          External Stream URL
                   |
                   v
           Integrated Player
                   |
                   v
              LIVE TV
```

## How It Works

1. The user opens the NexoTV website.
2. Available television channels are presented through the web interface.
3. The user selects a preferred television channel.
4. The application retrieves the associated streaming information.
5. The streaming URL is loaded into the integrated video player.
6. The user watches the available stream directly in the browser.

## Database Management

MySQL is used to store and manage the application's data.

Depending on the implemented database structure, channel records may contain:

| Field | Description |
|---|---|
| Channel ID | Unique channel identifier |
| Channel Name | Name of the television channel |
| Channel Logo | Channel logo or image reference |
| Category | Channel category |
| Streaming URL | External streaming source |
| Status | Channel availability or active status |

*The actual database schema may differ depending on the project implementation.*

## Streaming Functionality

NexoTV uses externally sourced streaming links to load television channels into the web player.

Potential supported formats depend on the player implementation and browser compatibility:

- HLS (`.m3u8`)
- MP4
- Other browser-compatible streaming formats

Streaming performance and availability depend on:

- External stream server availability.
- Internet connectivity.
- Browser compatibility.
- Media format support.
- Access restrictions imposed by streaming providers.

## Installation and Setup

### Requirements

Before installing the project, ensure that you have:

- PHP 8.0 or compatible version.
- MySQL or MariaDB.
- Apache or another PHP-compatible web server.
- A modern web browser.
- Internet connectivity for external streaming sources.

### 1. Clone the Repository

```bash
git clone YOUR_GITHUB_REPOSITORY_URL
```

### 2. Navigate to the Project Directory

```bash
cd NexoTV
```

### 3. Configure the MySQL Database

1. Open phpMyAdmin or your preferred MySQL management tool.
2. Create a database for NexoTV.
3. Import the project's SQL database file, if available.
4. Configure the database connection settings in the appropriate PHP configuration file.

Example database connection:

```php
<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "nexotv";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed");
}

?>
```

**Note:** Update the database credentials according to your local environment. This example is illustrative; use the connection method already implemented in the project.

### 4. Configure the Web Server

For XAMPP, place the project directory inside:

```text
C:\xampp\htdocs\NexoTV
```

Start Apache and MySQL from the XAMPP Control Panel.

### 5. Open the Application

Visit the following address in your browser:

```text
http://localhost/NexoTV/
```

The application should load once the server and database are configured correctly.

## Security Considerations

- Use prepared statements for database queries.
- Validate and sanitize user input.
- Protect database credentials and configuration files.
- Avoid exposing sensitive information in public repositories.
- Validate externally supplied streaming URLs.
- Use HTTPS for production deployment.
- Restrict database permissions appropriately.
- Use only streaming sources that you are legally authorized to access and distribute.

## Future Enhancements

Potential improvements for future versions include:

- Channel search and filtering.
- Channel categorization.
- Favorite channel management.
- Recently watched channels.
- Administrative dashboard for channel management.
- Stream availability monitoring.
- Improved video player controls.
- Enhanced mobile responsiveness.
- Better playback error handling.

## Project Objectives

The main objectives of NexoTV are:

1. Develop a centralized web platform for live television streaming.
2. Integrate PHP and MySQL for backend processing and data management.
3. Organize multiple television channel streaming links.
4. Provide browser-based playback for supported streams.
5. Simplify access to available online television channels.

## Content and Copyright Disclaimer

NexoTV is a platform for organizing and playing externally sourced streaming links.

The project does not claim ownership of third-party television channels, broadcasts, logos, trademarks, or copyrighted content.

Users and operators are responsible for ensuring that streaming sources are legally authorized for their intended use. Channel availability is dependent on the respective stream providers.

## Contributing

Contributions, suggestions, and improvements are welcome.

1. Fork the repository.
2. Create a new feature branch.
3. Implement your changes.
4. Commit your changes.
5. Submit a pull request.

## License

Specify an appropriate open-source license before distributing this project.

---

<p align="center">
  <strong>NexoTV – Bringing Live Television Streaming Together in One Platform.</strong>
</p>

<p align="center">
  <a href="https://sirc.liveblog365.com/?i=1">Visit Live Website</a>
</p>