/**
 * RaketGo - Main JavaScript File
 * Created and managed by Moesoft (Moeko Software)
 */

document.addEventListener('DOMContentLoaded', function() {
    initMotionSystem();
    initSearchFilters();
    initFormValidation();
    initNotifications();
    initMessages();
    initAutoScroll();
    initBackToTop();
    initRegionMapModal();
});

function initMotionSystem() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    document.body.classList.add('js-motion');

    var selectors = [
        '.panel',
        '.widget',
        '.filter-bar',
        '.auth-card',
        '.stat-card',
        '.quick-action',
        '.compact-job-item',
        '.update-item',
        '.site-info-item',
        '.notification-item',
        '.message-item',
        '.data-table tbody tr'
    ];

    var targets = document.querySelectorAll(selectors.join(','));
    if (!targets.length) {
        return;
    }

    var observer = null;
    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -8% 0px',
            threshold: 0.14
        });
    }

    targets.forEach(function(el, index) {
        el.classList.add('motion-reveal');
        el.style.setProperty('--reveal-delay', ((index % 8) * 34) + 'ms');

        if (observer) {
            observer.observe(el);
        } else {
            el.classList.add('is-visible');
        }
    });
}

// Search and Filter
function initSearchFilters() {
    var searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function(e) {
            performSearch(e.target.value);
        }, 300));
    }

    var filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            applyFilter(this.dataset.filter);
        });
    });
}

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

function performSearch(query) {
    if (query.length < 2) return;
    fetch('api/search.php?q=' + encodeURIComponent(query))
        .then(function(r) { return r.json(); })
        .then(function(data) { displaySearchResults(data); })
        .catch(function(err) { console.error('Search error:', err); });
}

function displaySearchResults(results) {
    var container = document.getElementById('search-results');
    if (!container) return;
    if (results.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No results found</p>';
        return;
    }
    container.innerHTML = results.map(createJobCard).join('');
}

function createJobCard(job) {
    return '<div class="compact-job-item" onclick="viewJobDetails(' + job.job_id + ')" style="cursor:pointer;">' +
        '<strong>' + escapeHtml(job.job_title) + '</strong>' +
        '<div class="text-muted" style="font-size:0.78rem;">' +
            '<i class="fas fa-building"></i> ' + escapeHtml(job.employer_name) +
            ' &middot; <i class="fas fa-map-marker-alt"></i> ' + escapeHtml(job.location_city) +
            ' &middot; <i class="fas fa-peso-sign"></i> ' + formatCurrency(job.pay_amount) +
        '</div>' +
        '<div style="margin-top:3px;">' + createTags(job.required_skills) + '</div>' +
    '</div>';
}

function createTags(skillsString) {
    if (!skillsString) return '';
    return skillsString.split(',').map(function(s) {
        return '<span class="tag tag-pink" style="font-size:0.6rem;">' + escapeHtml(s.trim()) + '</span>';
    }).join(' ');
}

function viewJobDetails(jobId) {
    window.location.href = 'job-details.php?id=' + jobId;
}

function applyFilter(filterType) {
    var url = new URL(window.location.href);
    url.searchParams.set('filter', filterType);
    window.location.href = url.toString();
}

// Form validation
function initFormValidation() {
    var forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) e.preventDefault();
        });
    });
}

function validateForm(form) {
    var isValid = true;
    form.querySelectorAll('[required]').forEach(function(field) {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });

    form.querySelectorAll('input[type="email"]').forEach(function(field) {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });

    form.querySelectorAll('input[name="mobile_number"]').forEach(function(field) {
        if (field.value && !isValidPhilippineMobile(field.value)) {
            showFieldError(field, 'Please enter a valid Philippine mobile number');
            isValid = false;
        }
    });

    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    var errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#721C24';
    errorDiv.style.fontSize = '0.78rem';
    errorDiv.style.marginTop = '0.2rem';
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    var err = field.parentNode.querySelector('.field-error');
    if (err) err.remove();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhilippineMobile(mobile) {
    return /^(09|\+639)\d{9}$/.test(mobile.replace(/\s/g, ''));
}

// Notifications
function initNotifications() {
    if (document.querySelector('.navbar')) {
        setInterval(checkNewNotifications, 30000);
    }
}

function checkNewNotifications() {
    fetch('api/check-notifications.php')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.unread_count !== undefined) updateNotificationBadge(data.unread_count);
        })
        .catch(function() {});
}

function updateNotificationBadge(count) {
    // Current header markup uses a dot indicator (<span class="notif-dot">) rather than a numeric .badge.
    var dot = document.querySelector('.nav-menu a[href="notifications.php"] .notif-dot');
    if (!dot) return;

    if (count > 0) {
        dot.style.display = 'inline';
    } else {
        dot.style.display = 'none';
    }
}


// Messages
function initMessages() {
    var items = document.querySelectorAll('.message-item[data-user-id]');
    items.forEach(function(item) {
        item.addEventListener('click', function() {
            window.location.href = 'messages.php?user=' + this.dataset.userId;
        });
    });
}

// Auto-scroll chat
function initAutoScroll() {
    var chat = document.getElementById('chat-messages');
    if (chat) chat.scrollTop = chat.scrollHeight;
}

// Back to top
function initBackToTop() {
    var backToTopBtn = document.getElementById('back-to-top');
    if (!backToTopBtn) return;

    var toggleButton = function() {
        if (window.scrollY > 320) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    };

    window.addEventListener('scroll', toggleButton, { passive: true });
    toggleButton();

    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function initRegionMapModal() {
    var modal = document.getElementById('region-map-modal');
    if (!modal) return;

    var openBtn = document.querySelector('.js-open-region-map');
    var closeTargets = modal.querySelectorAll('.js-close-region-map');

    if (openBtn) {
        openBtn.addEventListener('click', openRegionMapModal);
    }

    closeTargets.forEach(function(target) {
        target.addEventListener('click', closeRegionMapModal);
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeRegionMapModal();
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.js-open-region-map')) {
            openRegionMapModal();
        }
    });
}

function openRegionMapModal() {
    var modal = document.getElementById('region-map-modal');
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('region-map-open');
}

function closeRegionMapModal() {
    var modal = document.getElementById('region-map-modal');
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('region-map-open');
}

// Utilities
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function confirmAction(message, callback) {
    if (confirm(message)) callback();
}

function showAlert(message, type) {
    type = type || 'info';
    var alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-' + type;
    alertDiv.innerHTML = '<i class="fas fa-info-circle"></i> ' + escapeHtml(message);
    var container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        setTimeout(function() { alertDiv.remove(); }, 5000);
    }
}

// Skill tags input
function initSkillTags() {
    var skillInput = document.querySelector('input[name="skills"]');
    var container = document.querySelector('.skills-tags');
    if (!skillInput || !container) return;

    var skills = [];
    skillInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            var skill = this.value.trim();
            if (skill && skills.indexOf(skill) === -1) {
                skills.push(skill);
                addSkillTag(skill, container, skills);
                this.value = '';
            }
        }
    });
}

function addSkillTag(skill, container, skillsArray) {
    var tag = document.createElement('span');
    tag.className = 'tag tag-pink';
    tag.innerHTML = escapeHtml(skill) + ' <i class="fas fa-times" style="margin-left:0.3rem;cursor:pointer;"></i>';
    tag.querySelector('i').addEventListener('click', function() {
        var idx = skillsArray.indexOf(skill);
        if (idx > -1) skillsArray.splice(idx, 1);
        tag.remove();
    });
    container.appendChild(tag);
}
