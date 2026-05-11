-- 002_seed_data.sql
-- Initial dummy data for Al-Yasmin project

USE alyasmin_db;

-- 1. Insert Admin and Users (Password is 'password' bcrypt hashed)
INSERT INTO users (name, email, phone, password_hash, role, status) VALUES
('مدير الياسمين', 'admin@alyasmin.sy', '+963955500001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('أحمد العميل', 'ahmad@example.com', '+963955500002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('سارة العائدة', 'sara@example.com', '+963955500003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active');

-- 2. Insert Properties
INSERT INTO properties (title, description, location, image_url, type, rooms, size_m2, floor, price, listing_type, status, amenities_json) VALUES
('شقة فاخرة 3 غرف نوم مع إطلالة على الوادي', 'شقة واسعة مع إطلالات خلابة على وادي الجوز. تشطيب عصري.', 'وادي الجوز، حماة', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80', 'residential', 3, 140.5, 4, 350.00, 'rent', 'available', '["parking", "elevator", "generator", "balcony"]'),
('شقة مريحة 2 غرف نوم في الوسط', 'وحدة عائلية مريحة تقع بالقرب من بوابة المشروع الرئيسية.', 'الحي التجاري، حماة', 'https://images.unsplash.com/photo-1502672260266-1c1de2d93688?auto=format&fit=crop&w=900&q=80', 'residential', 2, 95.0, 2, 220.00, 'rent', 'available', '["parking", "elevator"]'),
('بنتهاوس فاخر للبيع', 'معيشة في الطابق العلوي مع تراس خاص وتخزين ماء.', 'الطابق الأخير، حماة', 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=900&q=80', 'residential', 4, 210.0, 8, 85000.00, 'sale', 'available', '["parking", "elevator", "generator", "water storage", "smart home"]');

-- 3. Insert Property Images (Assuming default placeholders)
INSERT INTO property_images (property_id, image_url, is_primary) VALUES
(1, 'public/images/apt1_main.jpg', TRUE),
(1, 'public/images/apt1_sub.jpg', FALSE),
(2, 'public/images/apt2_main.jpg', TRUE),
(3, 'public/images/apt3_main.jpg', TRUE);

-- 4. Insert Initial Bookings
INSERT INTO bookings (user_id, property_id, type, contract_type, start_date, occupants, total_amount, contract_id, status) VALUES
(2, 1, 'rent', 'monthly', '2025-05-01', 4, 350.00, 'CON-2025-1001', 'completed'),
(3, 2, 'rent', 'annual', '2025-06-01', 2, 2640.00, 'CON-2025-1002', 'confirmed');

-- 5. Insert Payments
INSERT INTO payments (booking_id, method, amount, transaction_ref, status) VALUES
(1, 'card', 350.00, 'TRX-9901-2025', 'completed'),
(2, 'transfer', 660.00, 'TRX-9902-2025', 'completed');

-- 6. Insert Reviews
INSERT INTO reviews (user_id, property_id, rating, comment, status) VALUES
(2, 1, 5, 'أوصي بهذه الشقة بشدة. نظيفة ومحافظ عليها جيداً.', 'approved');
