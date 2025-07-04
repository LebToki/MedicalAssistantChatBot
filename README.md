# Medical Doctor AI Helper Bot

Medical Doctor AI Helper Bot is an AI-powered chatbot designed to provide assistance and information on various medical topics. It can answer questions, provide recommendations, and offer support to users (Medical Staff) seeking Quick Medical Advice that could help them narrow down their exploration. It is important to note that this is a concept project and its functionality relies on indexed data. While it aims to mimic an AI Bot, it does not incorporate complex LLM or NLP techniques at this stage.

![Landing Screen](MedicalAssistantAiBot-screenshot.jpg)

## Features

- Interactive chat interface for users to communicate with the chatbot.
- Utilizes a database of predefined questions and responses for accurate and relevant answers.
- Supports real-time interaction and provides instant responses.
- Built with PHP and MySQL for server-side processing and storage.
- User-friendly interface with a clean and intuitive design.

## Getting Started

To get started with the Medical Doctor AI Helper Bot, follow these steps:

1. Clone the repository to your local machine.
2. Set up a local web server environment (e.g., Apache, Nginx, LARAGON/MAMP or XAMPP stacks).
3. Import the database structure and sample data using the provided SQL file. Feel free to import your own Q/A.
4. Run `composer install` to install PHP dependencies.
5. Create a `.env` file with your database credentials (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).
6. Open the application in a web browser to start interacting with the chatbot.
7. The system has been tested with over 200k medical questions and answers using prepared statements and indexing.
8. Input sanitization helps mitigate SQL injection attacks.

The application automatically calculates a `BASE_URL` constant at runtime to avoid hard-coded links.

## Directory Structure

- `index.php`: The main file that contains the chatbot interface and handles user interactions.

- `bot.php`: Handles the server-side processing of user messages and retrieves appropriate responses from the database.

- `ingest.php`: Imports data from JSON files into the chatbot's database for expanding its knowledge base.

- `fetch_response.php`: Retrieves a random response from the database for displaying in the chatbot interface.

- `training/`: Directory containing JSON files with additional data to be ingested into the chatbot's database.

- `css/`: Directory containing CSS stylesheets for styling the chatbot interface.

- `assets/`: Directory containing images and other static assets used in the application.

## Configure the database connection details in the database_config.php file
**database_config.php**

```<npm>
composer require vlucas/phpdotenv
```

to get this working

```<php>
// Load environment variables from a .env file if available

composer require vlucas/phpdotenv
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

**Database host**
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

**Database username** match yours
define('DB_USER', getenv('DB_USER') ?: 'root');

**Database password**  match yours
define('DB_PASS', getenv('DB_PASS') ?: '');

**Database name**  match yours
define('DB_NAME', getenv('DB_NAME') ?: 'phpbot');
```



Open the application in a web browser to start interacting with the chatbot.

The system is tested with over 200k Medical Questions and Answers with full prepared statements and indexing.



## Changelog

### Version 2.0 (2024-05-25)
Bug Fixes:

Fixed issue with Dotenv class not found by including the Composer autoload file.
Resolved SQLSTATE[42S22] error by removing the non-existent relevance column in the ORDER BY clause.
Improvements:

Enhanced fetch_response.php to correctly query available columns and handle search operations.
Improved error handling and added debug points to ingest.php for better diagnostics.
Optimized the script to provide meaningful error messages and trace the execution path.
Features:

Added a file drop area to ingest.php for uploading JSON files with drag-and-drop functionality.
Introduced a spinner to indicate processing status during file uploads.
Implemented a progress bar to show the upload progress visually.
Enhanced user experience by centering the file drop area on the page for better accessibility.
Added detailed debug points in ingest.php to trace the script execution and identify potential issues.
Cosmetic Changes:

Updated the CSS to ensure the file drop area is centered both vertically and horizontally.
Improved the visual styling of the ingest.php page to match the overall design of the application.
Miscellaneous:

Ensured that the AJAX request handling in ingest.php is robust and provides accurate feedback to the user.
Simplified the form data handling and streamlined the file upload process.
Summary
These updates aim to enhance the stability, usability, and visual appeal of the Medical Doctor AI Assistant Bot, providing a smoother and more intuitive experience for users while addressing key bugs and introducing useful features.


### v1.2 - Feature Update (2024-04-14)

**Key Enhancements:**
- Prepared Statements: Utilized across all SQL operations to enhance security and prevent SQL injection.
- Database Connection Error Handling: Immediate checks for errors upon database connection attempts to ensure reliability and prompt error reporting.
- Refactoring: Moved core functionalities into a separate function, getChatbotResponse, to improve the readability and maintainability of the code.
- Separation of Database Config: Shifted database configuration settings to a separate file to centralize database management settings and enhance security.

**Database Structure and Performance Improvements:**
- Table Changes and Index Introduction: Enhanced database table structures and introduced indexes to optimize data retrieval and performance.
- Full-text Search: Recommended for efficient text searching capabilities, enabling complex query handling and improved search performance.
- Normalization: Proposed database normalization to reduce redundancy by dividing data into related tables, enhancing data integrity and performance.
- Database Engine Recommendations: Suggested appropriate database engines (e.g., InnoDB, MyISAM) based on transaction support needs and read/write speed requirements.

**Script Specific Updates:**
- fetch_response.php:
  - Function Documentation: Added documentation for searchAnswers to clarify its purpose and usage.
  - SQL Query Optimization: Ensured SQL queries are optimized for performance, particularly for sorting and searching large datasets.
  - Encapsulation and Modularity: Encouraged the use of classes or separate functions for database interactions to simplify code management.
- index.php:
  - JavaScript Consolidation: Unified JavaScript into a single block for better management and reduced HTTP requests.
  - Error Handling Improvements: Corrected the placement of .catch() for fetch operations to ensure errors are appropriately caught and handled.
- ingest.php:
  - File Filtering: Configured to process only .json files to avoid unnecessary computation.
  - Error Checks: Enhanced error handling for JSON decoding to ensure robust data processing.
  - Database Interaction Optimization: Prepared SQL statements outside loops for improved efficiency and reduced processing overhead.

**General Changes:**
- UI and Interaction Enhancements: Improved user interface feedback and interaction, ensuring that elements like buttons provide immediate and intuitive feedback to actions.
- Performance Enhancements: Adjusted scripts and styles to optimize load times and interaction responsiveness.
- This summary encapsulates all major and critical updates made in version 1.2, providing a clear view of improvements and optimizations intended to enhance the functionality, security, and user experience of the Medical Doctor AI Helper Bot.

### v1.1.0 - Feature Update (2023-06-09)
- Added progress bar and visual feedback during data ingestion.
- Improved error handling and error reporting during ingestion.
- Implemented input sanitization to enhance security.
- Updated CSS styles for better responsiveness on small screens and tablets.
- Fixed bugs and minor issues reported by users.

### v1.0.0 - Initial Release 
- Implemented basic chatbot functionality.
- Set up database structure and imported initial data.
- Created user interface for chat interactions.
- Added ingestion script for importing additional data.
- Implemented server-side processing of user messages.

## License

This project is licensed under the Attribution License. 
<p xmlns:cc="http://creativecommons.org/ns#" >This work by <a rel="cc:attributionURL dct:creator" property="cc:attributionName" href="https://2tinteractive.com">Tarek Tarabichi</a> is licensed under <a href="http://creativecommons.org/licenses/by/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY 4.0<img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"><img style="height:22px!important;margin-left:3px;vertical-align:text-bottom;" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1"></a></p>
