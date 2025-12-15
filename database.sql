-- قاعدة بيانات منصة المصممين
CREATE DATABASE IF NOT EXISTS designer_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE designer_platform;

-- جدول المستخدمين
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('designer', 'client', 'admin') NOT NULL,
    phone VARCHAR(20),
    profile_image VARCHAR(255) DEFAULT 'default-avatar.png',
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول بيانات المصممين
CREATE TABLE designers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    programs TEXT, -- JSON array of programs
    whatsapp VARCHAR(20),
    instagram VARCHAR(100),
    twitter VARCHAR(100),
    facebook VARCHAR(100),
    linkedin VARCHAR(100),
    portfolio_url VARCHAR(255),
    paypal_email VARCHAR(150),
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_orders INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- جدول بيانات العملاء
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_orders INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- جدول الخدمات
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    related_programs TEXT, -- JSON array
    icon VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الطلبات
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    designer_id INT,
    service_id INT NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'in_progress', 'completed', 'delivered', 'cancelled') DEFAULT 'pending',
    price DECIMAL(10,2),
    designer_note TEXT,
    client_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (designer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- جدول التقييمات
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    designer_id INT NOT NULL,
    client_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (designer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
);

-- جدول الإشعارات
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_order_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- إدراج خدمات افتراضية
INSERT INTO services (name, description, category, related_programs, icon) VALUES
('تصميم شعار', 'تصميم شعار احترافي لعلامتك التجارية', 'تصميم جرافيكي', '["Photoshop", "Illustrator", "CorelDRAW"]', 'logo'),
('تصميم بوستر', 'تصميم بوستر إعلاني جذاب', 'تصميم جرافيكي', '["Photoshop", "Illustrator", "Canva"]', 'poster'),
('تصميم بنر إعلاني', 'تصميم بنرات للإعلانات الرقمية', 'تصميم جرافيكي', '["Photoshop", "Illustrator"]', 'banner'),
('تصميم هوية بصرية', 'تصميم هوية بصرية متكاملة للشركات', 'تصميم جرافيكي', '["Photoshop", "Illustrator", "InDesign"]', 'branding'),
('تصميم موشن جرافيك', 'تصميم فيديو موشن جرافيك احترافي', 'موشن جرافيك', '["After Effects", "Premiere Pro"]', 'motion'),
('تصميم واجهة مستخدم', 'تصميم واجهة مستخدم UI/UX', 'تصميم واجهات', '["Figma", "Adobe XD", "Sketch"]', 'ui'),
('تصميم صورة شخصية', 'تصميم وتعديل الصور الشخصية', 'تصميم صور', '["Photoshop", "Lightroom"]', 'portrait'),
('تصميم غلاف كتاب', 'تصميم غلاف احترافي للكتب', 'تصميم جرافيكي', '["Photoshop", "Illustrator", "InDesign"]', 'book'),
('تصميم بطاقة عمل', 'تصميم بطاقة عمل احترافية', 'تصميم جرافيكي', '["Photoshop", "Illustrator"]', 'card'),
('تعديل وتحسين الصور', 'تعديل وتحسين جودة الصور', 'تصميم صور', '["Photoshop", "Lightroom"]', 'edit');

-- إنشاء مستخدم أدمن افتراضي (كلمة المرور: admin123)
INSERT INTO users (username, email, password, user_type, phone) VALUES
('admin', 'admin@designer-platform.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '0000000000');
