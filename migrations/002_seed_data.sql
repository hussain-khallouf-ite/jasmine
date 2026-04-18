-- 002_seed_data.sql
-- Initial dummy data for Al-Yasmin project

USE alyasmin_db;

-- 1. Insert Admin and Users (Password is 'password123' bcrypt hashed)
INSERT INTO users (name, email, phone, password_hash, role, status) VALUES
('Admin Yasmin', 'admin@alyasmin.sy', '+963955500001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('Ahmad Customer', 'ahmad@example.com', '+963955500002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('Sara Returns', 'sara@example.com', '+963955500003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active');

-- 2. Insert Properties
INSERT INTO properties (title, description, type, rooms, size_m2, floor, price_per_month, status, amenities_json) VALUES
('Premium 3BR Wadi View', 'Spacious apartment with stunning views of Wadi al-Jawz. Modern finishing.', 'residential', 3, 140.5, 4, 350.00, 'available', '["parking", "elevator", "generator", "balcony"]'),
('Cozy 2BR Central', 'Comfortable family unit located near the main project gate.', 'residential', 2, 95.0, 2, 220.00, 'available', '["parking", "elevator"]'),
('Luxury Penthouse', 'Top floor living with private terrace and water storage.', 'residential', 4, 210.0, 8, 600.00, 'available', '["parking", "elevator", "generator", "water storage", "smart home"]');

-- 3. Insert Property Images (Assuming default placeholders)
INSERT INTO property_images (property_id, image_url, is_primary) VALUES
(1, 'public/images/apt1_main.jpg', TRUE),
(1, 'public/images/apt1_sub.jpg', FALSE),
(2, 'public/images/apt2_main.jpg', TRUE),
(3, 'public/images/apt3_main.jpg', TRUE);

-- 4. Insert Initial Bookings
INSERT INTO bookings (user_id, property_id, start_date, end_date, occupants, total_amount, status) VALUES
(2, 1, '2025-05-01', '2025-05-31', 4, 350.00, 'completed'),
(3, 2, '2025-06-01', '2025-08-31', 2, 660.00, 'confirmed');

-- 5. Insert Payments
INSERT INTO payments (booking_id, method, amount, transaction_ref, status) VALUES
(1, 'card', 350.00, 'TRX-9901-2025', 'completed'),
(2, 'transfer', 660.00, 'TRX-9902-2025', 'completed');

-- 6. Insert Reviews
INSERT INTO reviews (user_id, property_id, rating, comment, status) VALUES
(2, 1, 5, 'Highly recommend this apartment. Clean and well maintained.', 'approved');
