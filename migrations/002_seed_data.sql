-- 002_seed_data.sql
-- Initial dummy data for Al-Yasmin project
-- Note: Password for all users is 'password'

USE alyasmin_db;

-- 1. Insert Admin and Users
INSERT INTO users (name, email, phone, password_hash, role, status) VALUES
('مدير الياسمين', 'admin@alyasmin.sy', '+963955500001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('أحمد العميل', 'ahmad@example.com', '+963955500002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('سارة العائدة', 'sara@example.com', '+963955500003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('محمد السوري', 'mohammad@example.com', '+963955500004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('ليلى الحموية', 'laila@example.com', '+963955500005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('خالد النجار', 'khaled@example.com', '+963955500006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active');

-- 2. Insert Buildings
INSERT INTO buildings (name, description, floors, image_url, location, total_area_m2) VALUES
('برج الياسمين A', 'برج تجاري وسكني فاخر يضم مكاتب حديثة وشققاً سكنية راقية. يتميز بموقعه الاستراتيجي في قلب مركز الأعمال، ومصاعده السريعة، ونظام الحراسة المتكامل على مدار الساعة.', 10, 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=900&q=80', 'وادي الجوز - منطقة الأبراج التجارية', 12000.00),
('مجمع الياسمين B', 'مجمع سكني عائلي هادئ ومريح يقع بالقرب من الحديقة المركزية والملاعب الرياضية. مثالي للعائلات التي تبحث عن الراحة والأمان والقرب من الخدمات الأساسية.', 5, 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=900&q=80', 'وادي الجوز - الحي المتوسط (السنتر)', 6000.00),
('فيلا الياسمين C', 'مبنى سكني راقٍ ومحدود الارتفاع يتكون من شقق واسعة وبنتهاوس فخم مع تراسات واسعة وإطلالات ساحرة، وتحيط به حدائق خضراء خاصة وتجهيزات منزلية ذكية.', 3, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=900&q=80', 'وادي الجوز - حي الياسمين الشرقي', 3500.00);

-- 3. Insert Properties
-- Note: All properties are in Wadi al-Joz, divided into neighborhoods/sectors
INSERT INTO properties (building_id, title, description, location, image_url, type, rooms, size_m2, floor, price, listing_type, status, amenities_json) VALUES
-- Rent Properties
(2, 'شقة فاخرة 3 غرف نوم مع إطلالة على الوادي', 'شقة واسعة مع إطلالات خلابة على وادي الجوز. تشطيب عصري.', 'وادي الجوز - قطاع أ (إطلالة الوادي)', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80', 'residential', 3, 140.5, 4, 350.00, 'rent', 'available', '["parking", "elevator", "generator", "balcony"]'),
(2, 'شقة مريحة 2 غرف نوم في الوسط', 'وحدة عائلية مريحة تقع بالقرب من بوابة المشروع الرئيسية.', 'وادي الجوز - الحي المتوسط (السنتر)', 'https://images.unsplash.com/photo-1502672260266-1c1de2d93688?auto=format&fit=crop&w=900&q=80', 'residential', 2, 95.0, 2, 220.00, 'rent', 'available', '["parking", "elevator"]'),
(2, 'استوديو عصري مفروش', 'استوديو أنيق مفروش بالكامل مثالي للطلاب أو الموظفين.', 'وادي الجوز - حي الأكاديميين', 'https://images.unsplash.com/photo-1536376074432-ad427499eaa8?auto=format&fit=crop&w=900&q=80', 'residential', 1, 45.0, 1, 150.00, 'rent', 'available', '["wifi", "parking"]'),
(1, 'مكتب تجاري في برج الياسمين', 'مكتب واسع في منطقة حيوية، مناسب للشركات الناشئة.', 'وادي الجوز - منطقة الأبراج التجارية', 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=900&q=80', 'commercial', 4, 120.0, 5, 1200.00, 'rent', 'available', '["elevator", "security", "parking"]'),

-- Sale Properties
(1, 'بنتهاوس فاخر للبيع', 'معيشة في الطابق العلوي مع تراس خاص وتخزين ماء.', 'وادي الجوز - حي الفلل العلوي', 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=900&q=80', 'residential', 4, 210.0, 8, 85000.00, 'sale', 'available', '["parking", "elevator", "generator", "water storage", "smart home"]'),
(3, 'شقة عائلية كبيرة للبيع', 'شقة في منطقة هادئة مع حديقة صغيرة مشتركة.', 'وادي الجوز - حي الياسمين الشرقي', 'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=900&q=80', 'residential', 5, 180.0, 0, 75000.00, 'sale', 'available', '["parking", "garden"]'),
(1, 'محل تجاري للبيع', 'محل بموقع استراتيجي على الشارع الرئيسي.', 'وادي الجوز - شارع المعارض الرئيسي', 'https://images.unsplash.com/photo-1555529771-835f59fc5efe?auto=format&fit=crop&w=900&q=80', 'commercial', 2, 60.0, 0, 50000.00, 'sale', 'available', '["security", "frontage"]');

-- 4. Insert Initial Bookings
INSERT INTO bookings (user_id, property_id, type, contract_type, start_date, occupants, total_amount, contract_id, status) VALUES
(2, 1, 'rent', 'monthly', '2025-05-01', 4, 350.00, 'CON-2025-1001', 'completed'),
(3, 2, 'rent', 'annual', '2025-06-01', 2, 2640.00, 'CON-2025-1002', 'confirmed'),
(4, 3, 'rent', 'monthly', '2025-07-01', 1, 150.00, 'CON-2025-1003', 'pending'),
(5, 5, 'sale', NULL, '2025-08-01', NULL, 85000.00, 'CON-2025-1004', 'confirmed'),
(6, 7, 'sale', NULL, '2025-08-10', NULL, 50000.00, 'CON-2025-1005', 'pending');

-- 5. Insert Payments
INSERT INTO payments (booking_id, method, amount, transaction_ref, notes, recorded_by, status) VALUES
(1, 'card', 350.00, 'TRX-9901-2025', 'دفع إلكتروني عبر الموقع', NULL, 'completed'),
(2, 'transfer', 660.00, 'TRX-9902-2025', 'تحويل بنكي - الدفعة الأولى', NULL, 'completed'),
(4, 'cash', 85000.00, 'CASH-0001-2025', 'دفع نقدي بالكامل في المكتب', 1, 'completed');

-- 6. Insert Reviews
INSERT INTO reviews (user_id, property_id, rating, comment, status) VALUES
(2, 1, 5, 'أوصي بهذه الشقة بشدة. نظيفة ومحافظ عليها جيداً.', 'approved'),
(3, 2, 4, 'الموقع ممتاز جداً وقريب من كل الخدمات.', 'approved');
