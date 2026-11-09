-- ============================================================
-- IPTV Platform - Sample Channel Data
-- ============================================================
-- Run this SQL in phpMyAdmin after creating tables (schema.sql).
-- Inserts sample channels so the platform is not empty on first load.
--
-- FORMAT GUIDE:
--   .m3u8         → HLS live stream (auto-detect, tries direct then proxy)
--   .mp4/.webm    → Direct video file (native HTML5 player)
--   .html/.htm    → Web embed (loaded in iframe)
--   YouTube/Vimeo → Auto-detected as embed
--   Any other URL → Auto-detect mode (tries native first, then embed)
--
-- IMPORTANT: Replace these URLs with your actual working links!
-- ============================================================

USE `iptv_db`;

-- ============================================================
-- ENTERTAINMENT channels (HLS .m3u8 streams)
-- ============================================================
INSERT INTO `entertainment` (`name`, `logo`, `stream_url`) VALUES
('Capital TV', '', 'https://streaming.tvnepal.com:19360/capitaltv/capitaltv.m3u8'),
('Divya Darshan TV', '', 'https://streaming.tvnepal.com:19360/divyadarshan/divyadarshan.m3u8');

-- ============================================================
-- KIDS channels (mixed formats: HTML embed)
-- ============================================================
INSERT INTO `kids` (`name`, `logo`, `stream_url`) VALUES
('Sony YAY', '', 'https://multimovies.nl/sonyyay.html?id=1392');

-- ============================================================
-- NEWS channels (mixed formats)
-- ============================================================
INSERT INTO `news` (`name`, `logo`, `stream_url`) VALUES
('BBC News', '', 'https://multimovies.nl/bbcnews.html?id=1400');

-- ============================================================
-- GENERAL channels (for additional links - any format)
-- ============================================================
-- Add more channels here with any URL format:
--
-- HLS live stream (.m3u8):
-- INSERT INTO `general` (`name`, `logo`, `stream_url`) VALUES
-- ('Channel Name', 'https://example.com/logo.png', 'https://example.com/stream.m3u8');
--
-- MP4 video file:
-- INSERT INTO `movies` (`name`, `logo`, `stream_url`) VALUES
-- ('Movie Channel', '', 'https://example.com/video.mp4');
--
-- Web embed link:
-- INSERT INTO `general` (`name`, `logo`, `stream_url`) VALUES
-- ('Channel Name', '', 'https://multimovies.nl/channel.html?id=1234');
--
-- YouTube:
-- INSERT INTO `entertainment` (`name`, `logo`, `stream_url`) VALUES
-- ('My YouTube', '', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
--
-- Any unknown URL (auto-detects playback method):
-- INSERT INTO `general` (`name`, `logo`, `stream_url`) VALUES
-- ('Unknown Format', '', 'https://example.com/some/stream');
