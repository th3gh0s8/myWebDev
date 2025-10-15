-- Database table for storing FPS performance data for web page charts
CREATE TABLE fps_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fps_value DECIMAL(5,2) NOT NULL, -- Allows for precise FPS values (e.g., 59.97)
    session_id VARCHAR(255) DEFAULT NULL, -- To group measurements by user session
    page_url VARCHAR(500) DEFAULT NULL, -- Track which page the FPS was measured on
    user_agent TEXT DEFAULT NULL, -- Browser/device information for analysis
    user_id INT DEFAULT NULL, -- If you have user accounts
    device_type ENUM('desktop', 'mobile', 'tablet') DEFAULT NULL, -- Device category
    browser_name VARCHAR(100) DEFAULT NULL, -- Browser name
    os_info VARCHAR(100) DEFAULT NULL, -- Operating system
    viewport_width INT DEFAULT NULL, -- Viewport dimensions
    viewport_height INT DEFAULT NULL, -- Viewport dimensions
    INDEX idx_timestamp (timestamp),
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_page_url (page_url(255))
);

-- Optional: Add a composite index for common queries
-- This would be useful for filtering by both timestamp and page
CREATE INDEX idx_timestamp_page ON fps_performance (timestamp, page_url(255));

-- Example query to retrieve data for chart:
-- SELECT timestamp, fps_value FROM fps_performance 
-- WHERE page_url = 'your-page-url' 
-- AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
-- ORDER BY timestamp ASC;