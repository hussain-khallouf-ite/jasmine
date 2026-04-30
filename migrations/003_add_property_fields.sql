-- 003_add_property_fields.sql
-- Add missing listing fields to the properties table

USE alyasmin_db;

ALTER TABLE properties
    ADD COLUMN IF NOT EXISTS location VARCHAR(255) NOT NULL DEFAULT 'غير محددة' AFTER description,
    ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) AFTER location;

-- Example updates for existing rows, if needed:
-- UPDATE properties SET location = 'موقع غير محدد' WHERE location = 'غير محددة';
