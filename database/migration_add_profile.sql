-- Migration: Add profile picture and user activity tracking
-- Run this SQL if you already have the mervmaii database created

ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL COMMENT 'Profile picture filename in uploads/profiles/' AFTER anniversary_date;
ALTER TABLE users ADD COLUMN last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Track when user was last active' AFTER profile_picture;
