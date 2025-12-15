// ========================================
// تأثيرات الأنيميشن عند التمرير
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // إضافة تأثير الظهور التدريجي للعناصر
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // مراقبة العناصر
    const animatedElements = document.querySelectorAll('.service-card, .stat-card, .feature-card, .order-card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });

    // ========================================
    // تأكيد الحذف
    // ========================================
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من هذا الإجراء؟')) {
                e.preventDefault();
            }
        });
    });

    // ========================================
    // إخفاء الرسائل تلقائياً
    // ========================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100px)';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // ========================================
    // تحسين تجربة النماذج
    // ========================================
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'جاري المعالجة...';
                
                // إعادة تفعيل الزر بعد 3 ثواني في حال لم يتم التحويل
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.getAttribute('data-original-text') || 'إرسال';
                }, 3000);
            }
        });
    });

    // ========================================
    // تحسين تجربة رفع الصور
    // ========================================
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // التحقق من حجم الملف (أقل من 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('حجم الصورة كبير جداً. يرجى اختيار صورة أقل من 5 ميجابايت');
                    input.value = '';
                    return;
                }
                
                // معاينة الصورة
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.profile-image-large');
                    if (preview) {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // ========================================
    // تحسين نظام التقييم بالنجوم
    // ========================================
    const starRating = document.querySelector('.star-rating');
    if (starRating) {
        const stars = starRating.querySelectorAll('label');
        stars.forEach((star, index) => {
            star.addEventListener('mouseenter', function() {
                stars.forEach((s, i) => {
                    if (i >= index) {
                        s.style.color = '#f59e0b';
                    } else {
                        s.style.color = '#94a3b8';
                    }
                });
            });
        });
        
        starRating.addEventListener('mouseleave', function() {
            const checked = starRating.querySelector('input:checked');
            if (checked) {
                const checkedIndex = Array.from(starRating.querySelectorAll('input')).indexOf(checked);
                stars.forEach((s, i) => {
                    if (i >= checkedIndex) {
                        s.style.color = '#f59e0b';
                    } else {
                        s.style.color = '#94a3b8';
                    }
                });
            } else {
                stars.forEach(s => s.style.color = '#94a3b8');
            }
        });
    }

    // ========================================
    // تحسين البحث في الوقت الفعلي
    // ========================================
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // يمكن إضافة بحث AJAX هنا
                console.log('البحث عن:', input.value);
            }, 500);
        });
    });

    // ========================================
    // تحسين القوائم المنسدلة
    // ========================================
    const selectElements = document.querySelectorAll('select');
    selectElements.forEach(select => {
        select.addEventListener('change', function() {
            this.style.borderColor = '#8b5cf6';
            setTimeout(() => {
                this.style.borderColor = '';
            }, 1000);
        });
    });

    // ========================================
    // إضافة تأثيرات للأزرار
    // ========================================
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.5)';
            ripple.style.width = ripple.style.height = '100px';
            ripple.style.left = e.offsetX - 50 + 'px';
            ripple.style.top = e.offsetY - 50 + 'px';
            ripple.style.animation = 'ripple 0.6s ease-out';
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // ========================================
    // تحسين التنقل السلس
    // ========================================
    const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
    smoothScrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#' && targetId !== '') {
                e.preventDefault();
                const target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ========================================
    // تحديث الوقت بشكل ديناميكي
    // ========================================
    const timeElements = document.querySelectorAll('.order-date, .review-date');
    timeElements.forEach(element => {
        const dateText = element.textContent;
        // يمكن إضافة تحويل الوقت إلى صيغة نسبية (منذ ساعة، منذ يوم، إلخ)
    });

    // ========================================
    // إضافة تأثير التحميل
    // ========================================
    window.addEventListener('load', function() {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 0.5s ease';
            document.body.style.opacity = '1';
        }, 100);
    });
});

// ========================================
// CSS للأنيميشن الإضافية
// ========================================
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        from {
            transform: scale(0);
            opacity: 1;
        }
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ========================================
// دوال مساعدة
// ========================================
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.left = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// تصدير الدوال للاستخدام العام
window.showNotification = showNotification;
